Vue.config.devtools = true;

const app = new Vue({
    el: '#app',

    data() {
        return {
            muted: true,
            conn: null,
            availableUsers: [],
            joined: [],
            session: {
                clientId: '',
                username: '',
                auth: false,
            },
            pendingSync: false,
            joinedMidGame: false, // If use joins in the middle of someone else playing.
            chosenCard: null, // What card did the user choose
            votes: [], // Which users have voted
            votesData: [], // Which users voted what
            cards: [
                {
                    value: '0',
                },
                {
                    value: '0.5',
                },
                {
                    value: '1',
                },
                {
                    value: '2',
                },
                {
                    value: '3',
                },
                {
                    value: '5',
                },
                {
                    value: '8',
                },
                {
                    value: '13',
                },
                {
                    value: '20',
                },
                {
                    value: '40',
                },
                {
                    value: '100',
                },
                {
                    value: '?',
                },
                {
                    value: '☕️',
                },
            ]
        }
    },

    computed: {
        hasVoted() {
            return this.votes.indexOf(this.session.username) !== -1;
        },
    },

    mounted() {
        this.stopAudio();

        let clientId = window.localStorage.getItem('clientId');

        if (!clientId) {
            clientId = Math.random().toString(36).substring(2);
            window.localStorage.setItem('clientId', clientId);
        }

        this.session.clientId = clientId;

        this.openSocket();

        const lastVoteIndex = window.localStorage.getItem('lastVoteIndex');
        if (lastVoteIndex !== null && lastVoteIndex !== 'undefined' && parseInt(lastVoteIndex) >= 0) {
            this.chosenCard = parseInt(lastVoteIndex);
        }
    },

    methods: {
        startAudio() {
            if (this.$refs['audio'] && this.muted === false) {
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

        muteAudio() {
            if (this.$refs['audio'] && !this.$refs['audio'].paused) {
                this.stopAudio();
            }

            this.muted = !this.muted;

            if (this.muted === false && (this.pendingSync || (this.hasVoted && !this.votesData.length))) {
                this.startAudio();
            }
        },

        clearStorage() {
            window.localStorage.clear();
            window.location.reload(true);
        },

        openSocket() {
            this.connection = new WebSocket(window.PLANNINGPOKER.websocketUrl);

            this.connection.onopen = this.onOpen;
            this.connection.onmessage = this.onMessage;
            this.connection.onclose = () => {
                setTimeout(this.clearStorage(), 1000);
            };
            this.connection.onerror = function(e) { console.log('ERROR', e); };
        },

        send(type, data) {
            let json = {
                type,
                data
            };

            this.connection.send(JSON.stringify(json));
        },

        join() {
            if (this.session.username.trim() == '') {
                return;
            }

            this.send('join', this.session);
        },

        select(card) {
            this.chosenCard = this.cards.indexOf(card);
        },

        vote() {
            if (this.chosenCard === null || this.chosenCard < 0) {
                return;
            }

            this.send('vote', {
                clientId: this.session.clientId,
                username: this.session.username,
                vote: this.chosenCard,
            });

            window.localStorage.setItem('lastVoteIndex', this.chosenCard);

            this.votes.push(this.session.username);

            this.startAudio();
        },

        next() {
            this.startAudio();

            // Remove votes data
            this.chosenCard = null;
            this.votes = [];
            this.votesData = [];
            window.localStorage.setItem('lastVoteIndex', null);

            this.pendingSync = true;

            // Send next/"remove vote" message
            this.send('advance', this.session);
        },

        onOpen(e) {
            if (this.session.clientId !== '') {
                this.send('connect', this.session);
            }
        },

        onMessage(msg) {
            const {type, data} = JSON.parse(msg.data);

            switch (type) {
                case 'users':
                    this.availableUsers = data.users;
                    break;
                case 'login':
                    this.session = data.session;
                    this.pendingSync = (data.session.advanced === true);
                    this.joinedMidGame = (data.session.midgame_join === true);
                    this.joined = data.joined;
                    this.votes = data.votes;

                    // Make sure to update other connections, that this is user actually advanced
                    if (this.pendingSync) {
                        this.send('advance', this.session);
                    }

                    break;
                case 'join':
                    if (this.joined.indexOf(data.username) === -1) {
                        this.joined.push(data.username);
                    }
                    break;
                case 'midgame_join':
                    this.joinedMidGame = true;
                    break;
                case 'leave':
                    this.joined = data.joined;
                    this.votes = data.votes;

                    break;
                case 'vote':
                    if (this.votes.indexOf(data.username) === -1) {
                        this.votes.push(data.username);
                    }
                    break;
                case 'finish':
                    this.joinedMidGame = false;
                    this.pendingSync = false;

                    // Remove votes data
                    this.chosenCard = null;
                    this.votes = [];
                    this.votesData = [];
                    window.localStorage.setItem('lastVoteIndex', null);

                    this.stopAudio();

                    break;
                case 'showoff':
                    window.localStorage.setItem('lastVoteIndex', null);
                    this.chosenCard = null;
                    this.joinedMidGame = false;
                    this.votesData = data;

                    this.stopAudio();
                    break;
            }
        },
    }
});
