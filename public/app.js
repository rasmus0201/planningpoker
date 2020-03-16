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
            chosenCard: null,
            votes: [],
            cards: [
                {
                    value: '0',
                    votes: [],
                },
                {
                    value: '0.5',
                    votes: [],
                },
                {
                    value: '1',
                    votes: [],
                },
                {
                    value: '2',
                    votes: [],
                },
                {
                    value: '3',
                    votes: [],
                },
                {
                    value: '5',
                    votes: [],
                },
                {
                    value: '8',
                    votes: [],
                },
                {
                    value: '13',
                    votes: [],
                },
                {
                    value: '20',
                    votes: [],
                },
                {
                    value: '40',
                    votes: [],
                },
                {
                    value: '100',
                    votes: [],
                },
                {
                    value: '?',
                    votes: [],
                },
                {
                    value: '☕️',
                    votes: [],
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
    },

    methods: {
        openSocket() {
            this.connection = new WebSocket('ws://localhost:9000');

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
                username: this.session.username,
                vote: this.chosenCard,
            });

            this.votes.push(this.session.username);
        },

        next() {

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
            }
        },
    }
});
