Vue.config.devtools = true;

const app = new Vue({
    el: '#app',

    data() {
        return {
            conn: null,
            availableUsers: [],
            joined: [],
            session: {
                clientId: '',
                username: '',
                auth: false,
            },
            pendingSync: false,
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
        let clientId = window.localStorage.getItem('clientId');

        if (!clientId) {
            let clientId = Math.random().toString(36).substring(2);
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
        openSocket() {
            this.connection = new WebSocket(window.PLANNINGPOKER.websocketUrl);

            this.connection.onopen = this.onOpen;
            this.connection.onmessage = this.onMessage;
            this.connection.onclose = function(e) { console.log('CLOSE', e); };
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
        },

        next() {
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
                    this.pendingSync = (parseInt(data.session.advanced) !== 0);
                    this.joined = data.joined;
                    this.votes = data.votes;
                    break;
                case 'join':
                    if (this.joined.indexOf(data.username) === -1) {
                        this.joined.push(data.username);
                    }
                    break;
                case 'vote':
                    if (this.votes.indexOf(data.username) === -1) {
                        this.votes.push(data.username);
                    }
                    break;
                case 'finish':
                    this.pendingSync = false;
                    
                    // Remove votes data
                    this.chosenCard = null;
                    this.votes = [];
                    this.votesData = [];
                    window.localStorage.setItem('lastVoteIndex', null);
                    
                    break;
                case 'showoff':
                    this.votesData = data;
                    break;
            }
        },
    }
});
