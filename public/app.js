Vue.config.devtools = true;

const app = new Vue({
  el: '#app',

  data() {
    return {
      connection: null,
      retries: 0,
      session: {
        settings: {
          animatedBg: true,
          pin: 'guldfugl',
        },
        muted: true,
        clientId: '',
        username: '',
        userType: null,
        auth: false,
        chosenCard: {}, // What card did the user choose
      },
      game: {
        id: null,
        state: null,
        states: {
          NONE: null,
          LOBBY: 'LOBBY',
          PLAYING: 'PLAYING',
          SHOWOFF: 'SHOWOFF',
          FINISHED: 'FINISHED',
        },
        cards: window.PLANNINGPOKER.cards,
        defaultCustomCard: {
          type: 'user',
          value: ''
        },
        customCard: {
          type: 'user',
          value: ''
        },
        votingUsers: [], // Which users have voted
        availableUsers: [], // Users that have not been taking yet
        authenticatedPlayers: [], // Users that are authenticated in the game
        votes: [], // Actual votes/cards with data
      },
    };
  },

  computed: {
    hasVoted() {
      return this.game.votingUsers.indexOf(this.session.username) !== -1;
    },

    canVote() {
      return !this.hasVoted && this.session.chosenCard.type;
    },

    bodyClass() {
      if (this.showAdmin) {
        return 'default-bg';
      }

      const states = [this.game.states.LOBBY, this.game.states.NONE, this.game.states.FINISHED];
      if (this.session.auth && !states.includes(this.game.state)) {
        const mqStandAlone = '(display-mode: standalone), (prefers-color-scheme: dark)';
        if (navigator.standalone || window.matchMedia(mqStandAlone).matches) {
          return 'dark-bg';
        }

        return 'default-bg';
      }

      return this.session.settings.animatedBg ? 'animated-bg' : 'static-bg';
    },

    displayVotes() {
      const votes = [];
      const userVotes = [];

      const cardValuesMap = new Map(this.game.cards.map(card => [card.value, card.image]));
      for (const vote of this.game.votes) {
        const systemCard = cardValuesMap.get(vote.value);

        votes.push({
          ...vote,
          type: systemCard ? 'system' : 'user',
          image: systemCard ?? 'cover',
        });

        userVotes.push(vote.username);
      }

      for (const user of this.game.authenticatedPlayers) {
        if (userVotes.includes(user)) {
          continue;
        }

        votes.push({
          username: user,
          type: 'system',
          value: '-',
          image: 'question'
        });
      }

      return votes;
    },

    showAdmin() {
      return (
        this.connection !== null &&
        this.session.auth === true &&
        this.session.userType == 'gamemaster'
      );
    },
  },

  mounted() {
    if (!this.getSession()) {
      clientId = Math.random().toString(36).substring(2);

      this.session.clientId = clientId;
      this.saveSession();
    }

    this.session = Object.assign({}, JSON.parse(this.getSession()), {
      auth: false,
    });

    const localStorage = this.getStorage();
    if (localStorage) {
      this.session.settings = Object.assign({}, {
        muted: true,
        animatedBg: true,
        pin: 'guldfugl',
      }, JSON.parse(localStorage));
    }

    if (this.session.settings.pin.trim() !== '') {
      this.join();
    }

    // Can't play a song on first load (user interaction required)
    if (this.session.muted === false) {
      this.session.muted = true;
      this.saveSession();
    }
  },

  watch: {
    bodyClass: {
      handler(val) {
        document.body.className = val;
      },
      immediate: true
    }
  },

  methods: {
    startAudio() {
      if (this.$refs['audio']) {
        this.$refs['audio'].currentTime = 0;
        this.$refs['audio'].play();
      }
    },

    stopAudio() {
      if (this.$refs['audio']) {
        this.$refs['audio'].pause();
        this.$refs['audio'].currentTime = 0;
      }
    },

    toggleMute() {
      if (!this.$refs['audio']) {
        return;
      }

      if (this.session.muted) {
        this.startAudio();
        this.session.muted = false;
      } else {
        this.stopAudio();
        this.session.muted = true;
      }

      this.saveSession();
    },

    clearStorage() {
      window.localStorage.clear();
      window.sessionStorage.clear();
      window.location.reload();
    },

    toggleBgAnimation() {
      this.session.settings.animatedBg = !this.session.settings.animatedBg;
      this.saveStorage();
    },

    saveSession() {
      window.sessionStorage.setItem('session', JSON.stringify(this.session));
    },

    getSession() {
      return window.sessionStorage.getItem('session');
    },

    saveStorage() {
      window.localStorage.setItem('storage', JSON.stringify(this.session.settings));
    },

    getStorage() {
      return window.localStorage.getItem('storage');
    },

    isChosenCard(card) {
      return this.session.chosenCard?.value === card.value &&
        this.session.chosenCard?.type === card.type;
    },

    join() {
      if (this.session.settings.pin.trim() == '') {
        window.alert('Type game pin');

        return;
      }

      this.openSocket(this.session.settings.pin, this.session.clientId);
      this.saveStorage();
      this.saveSession();
    },

    openSocket(channel, clientId) {
      this.connection = new WebSocket(
        `${window.PLANNINGPOKER.websocketUrl}?channel=${channel}&session[gamepin]=${channel}&session[clientId]=${clientId}`
      );

      this.connection.onopen = this.onOpen;
      this.connection.onmessage = this.onMessage;
      this.connection.onclose = () => {
        if (this.retries < 3) {
          this.retries++;
          this.join();
        } else {
          console.log('Could not automatically start WebSocket connection');
        }
      };
      this.connection.onerror = function (e) {
        console.log('ERROR', e);
      };
    },

    send(type, data) {
      let json = {
        type,
        data,
      };

      this.connection.send(JSON.stringify(json));
    },

    select(card) {
      this.session.chosenCard = card;
      this.saveSession();
    },

    resetChosenCard() {
      this.select({});
      this.game.customCard = {
        type: 'user',
        value: ''
      };
    },

    hasUserVoted(username) {
      return this.game.votingUsers.indexOf(username) !== -1;
    },

    login() {
      if (this.session.username === '') {
        window.alert('Select user');
        return;
      }

      this.send('login', this.session);
      this.saveSession();
    },

    vote() {
      if (!this.canVote) {
        return;
      }

      console.log(JSON.stringify(this.session.chosenCard));

      this.send('vote', {
        clientId: this.session.clientId,
        username: this.session.username,
        vote: this.session.chosenCard.value,
      });
    },

    startGame() {
      this.advanceRound();

      this.send('startGame', {
        clientId: this.session.clientId,
      });
    },

    finishGame() {
      this.send('finishGame', {
        clientId: this.session.clientId,
      });
    },

    advanceRound() {
      this.send('advanceRound', {
        clientId: this.session.clientId,
      });
    },

    finishRound() {
      this.send('finishRound', {
        clientId: this.session.clientId,
      });
    },

    onOpen(e) {
      // Automatic login if user have selected username before
      if (this.session.username !== '') {
        this.login();
      }
    },

    onMessage(msg) {
      const { type, data } = JSON.parse(msg.data);

      switch (type) {
        case 'setGame':
          if (data.id) {
            this.game.id = data.id;
          }

          if (data.state) {
            if ([null, this.game.state].includes(this.game.state) === false && data.state === this.game.states.PLAYING) {
              this.resetChosenCard();
            }

            this.game.state = data.state;
          }
          break;

        case 'setVotingUsers':
          this.game.votingUsers = data.users;

          break;

        case 'setAuthenticatedPlayers':
          this.game.authenticatedPlayers = data.users;
          break;

        case 'setAvailableUsers':
          this.game.availableUsers = data.users;
          break;

        case 'setSessionData':
          if (data.auth) {
            this.session.auth = data.auth;
          }

          if (data.userType) {
            this.session.userType = data.userType;
          }

          if (this.session.chosenCard.type === 'user') {
            this.game.customCard = this.session.chosenCard;
          }

          this.saveSession();
          break;

        case 'setVotes':
          this.resetChosenCard();
          this.game.votes = data.votes;
          break;

        case 'setVote':
          if (data.vote !== null) {
            if (this.game.cards.filter(({ value }) => value === data.vote).length === 1) {
              this.select({
                type: 'system',
                value: data.vote
              });
            } else {
              this.select({
                type: 'user',
                value: data.vote
              });
            }

            if (!this.hasVoted) {
              this.game.votingUsers.push(this.session.username);
            }
          } else {
            this.resetChosenCard();

            if (this.hasVoted) {
              this.votes.splice(
                this.game.votingUsers.indexOf(this.session.username),
                1
              );
            }
          }

          break;
      }
    },
  },
});
