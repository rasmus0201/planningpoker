<?php

// require_once '../bootstrap/migrations.php';

$websocketScheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') ? 'ws' : 'wss';

$websocketMap = [
    '127.0.0.1' => '://127.0.0.1:9000',
    '165.227.174.67' => '://planningpoker.rasmusbundsgaard.dk/websocket',
];

$websocketConnection = isset($websocketMap[$_SERVER['SERVER_ADDR']]) ? $websocketMap[$_SERVER['SERVER_ADDR']] : $websocketMap['127.0.0.1'];
$websocketConnection = $websocketScheme . $websocketConnection;
?>
<!DOCTYPE html>
<html lang="da" dir="ltr">

<head>
    <meta charset="utf-8">
    <title>Planning Poker</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="app.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        window.PLANNINGPOKER = {
            websocketUrl: "<?php echo $websocketConnection; ?>",
            cards: [{
                    type: 'system',
                    value: '1',
                    image: '1',
                },
                {
                    type: 'system',
                    value: '2',
                    image: '2',
                },
                {
                    type: 'system',
                    value: '3',
                    image: '3',
                },
                {
                    type: 'system',
                    value: '5',
                    image: '5',
                },
                {
                    type: 'system',
                    value: '8',
                    image: '8',
                },
                {
                    type: 'system',
                    value: '13',
                    image: '13',
                },
                {
                    type: 'system',
                    value: '20',
                    image: '20',
                },
                {
                    type: 'system',
                    value: '40',
                    image: '40',
                },
                {
                    type: 'system',
                    value: '100',
                    image: '100',
                },
                {
                    type: 'system',
                    value: '∞',
                    image: 'infinite',
                },
                {
                    type: 'system',
                    value: '?',
                    image: 'question',
                },
                {
                    type: 'system',
                    value: '🍰',
                    image: 'brownie',
                },
                {
                    type: 'system',
                    value: '☕️',
                    image: 'coffee',
                },

            ],
        };
    </script>
</head>

<body>
    <div id="app" class="container-fluid" :class="game.state" v-cloak>
        <audio loop ref="audio">
            <source src="music.mp3" type="audio/mp3">
        </audio>

        <template v-if="session.auth === false">
            <div v-if="game.state === game.states.NONE || !connection" class="join animated-bg">
                <form class="d-flex" @submit.prevent="join">
                    <input type="tel" placeholder="Game Pin" class="form-control" name="password" v-model="session.pin" />
                    <button type="submit" class="btn btn-primary ml-2">Join!</button>
                </form>
            </div>
            <div v-else-if="game.state === game.states.LOBBY || connection" class="lobby animated-bg">
                <div class="d-flex">
                    <select class="form-control mr-2" v-model="session.username" :disabled="game.availableUsers.length === 0">
                        <option value="">Vælg bruger</option>
                        <option v-for="(user, index) in game.availableUsers" :key="'option-user-'+index">
                            {{ user }}
                        </option>
                    </select>
                    <button @click="login" class="btn btn-primary">Ok!</button>
                </div>
            </div>
        </template>
        <template v-if="session.auth">
            <div v-if="showAdmin" class="container-fluid pr-md-5">
                <div class="row mt-3">
                    <div class="col-12 col-md-4">
                        <h1 class="mb-4">Game Master Control Panel</h1>
                        <div class="mb-3">
                            <span class="d-inline-block p-2 mb-2 bg-secondary text-white" :class="{ 'bg-success' : [game.states.LOBBY, game.states.PLAYING, game.states.SHOWOFF, game.states.FINISHED].includes(game.state) }">LOBBY</span>
                            <span class="d-inline-block p-2 mb-2">></span>
                            <span class="d-inline-block p-2 mb-2 bg-secondary text-white" :class="{ 'bg-success' : [game.states.PLAYING, game.states.SHOWOFF, game.states.FINISHED].includes(game.state) }">PLAYING / SHOWOFF</span>
                            <span class="d-inline-block p-2 mb-2">></span>
                            <span class="d-inline-block p-2 mb-2 bg-secondary text-white" :class="{ 'bg-success' : game.state == game.states.FINISHED }">FINISHED</span>
                        </div>
                        <div class="mb-4">
                            <div class="mb-2">
                                Game Pin: <span class="badge text-white bg-primary">{{ session.pin }}</span>
                            </div>
                            <div class="mb-2">Connected users:</div>
                            <template v-for="user in game.authenticatedPlayers">
                                <span :key="user" class="d-inline-block p-2 mr-1 mb-1 rounded" :class="hasUserVoted(user) ? ['bg-success', 'text-white'] : ['bg-light']">{{ user }}</span>
                            </template>
                        </div>
                        <div class="mb-4">
                            <button v-if="[game.states.LOBBY, game.states.FINISHED].includes(game.state)" @click="startGame()" class="btn btn-success mb-2">
                                Start Planning Poker
                            </button>
                            <button v-else-if="game.state == game.states.PLAYING" class="btn btn-primary mb-2" @click="finishRound()">
                                Force 'showoff'
                            </button>
                            <button v-else-if="game.state == game.states.SHOWOFF" @click="advanceRound()" class="btn btn-success mb-2">
                                Continue to new round
                            </button>
                            <br>
                            <button class="btn btn-danger" :disabled="game.state == game.states.FINISHED" @click="finishGame()">
                                Finish game
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-md-8">
                        <p v-if="game.state !== game.states.SHOWOFF">
                            Showing votes when all players have picked a card, or button "force showoff" is clicked.
                        </p>
                        <div v-if="game.state === game.states.SHOWOFF" class="pcard-container">
                            <div class="pcard" v-for="(vote, index) in displayVotes" :key="'vote-'+index">
                                <div :class="['pcard__inner', 'pcard__image', `pcard__image--${vote.image}`]">
                                    <div class="pcard__symbol pcard__symbol--big">
                                        <h4 class="pcard__title">{{ vote.username }}</h4>
                                        <p v-if="vote.type === 'user'" class="pcard__value">{{ vote.value }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else-if="game.state === game.states.LOBBY" class="lobby animated-bg">
                <h1 class="text-white mb-0">Lobby</h1>
                <div class="p-5">
                    <span v-for="user in game.authenticatedPlayers" :key="user" class="p-3 m-2 rounded bg-white font-weight-bold">{{
                        user
                    }}</span>
                </div>
            </div>
            <div v-else-if="game.state === game.states.PLAYING" class="ma-3">
                <div class="pcard-container">
                    <button class="pcard" :disabled="hasVoted" :class="{ 'pcard--active' : isChosenCard(card) }" @click="select(card)" v-for="(card, index) in game.cards" :key="'card-'+index">
                        <div :class="['pcard__inner', 'pcard__image', `pcard__image--${card.image}`]">
                            <div class="pcard__symbol pcard__symbol--big sr-only">{{ card.value }}</div>
                        </div>
                    </button>
                    <button class="pcard" :disabled="hasVoted" :class="{ 'pcard--active' : isChosenCard(game.customCard) }" @click="select(game.customCard)">
                        <div class="pcard__inner">
                            <div class="pcard__symbol pcard__symbol--big">
                                <span class="bg-white rounded">
                                    <input v-if="hasVoted && isChosenCard(game.customCard)" type="text" disabled class="pcard__input" v-model="session.chosenCard.value ?? ''">
                                    <input v-else-if="hasVoted" type="text" disabled class="pcard__input" placeholder="✏️">
                                    <input v-else type="text" class="pcard__input" v-model="game.customCard.value" @blur="select(game.customCard)" placeholder="✏️" maxlength="12">
                                </span>
                            </div>
                        </div>
                    </button>
                </div>
                <div class="pb-5 mb-5"><br><br><br></div>
                <button class="btn btn-primary btn-huge snap-bottom" @click="vote" :disabled="hasVoted">Vote!</button>
            </div>
            <div v-else-if="game.state === game.states.SHOWOFF" class="mt-5 mt-md-2">
                <div class="pcard-container">
                    <div class="pcard" v-for="(vote, index) in displayVotes" :key="'vote-'+index">
                        <div :class="['pcard__inner', 'pcard__image', `pcard__image--${vote.image}`]">
                            <div class="pcard__symbol pcard__symbol--big">
                                <h4 class="pcard__title">{{ vote.username }}</h4>
                                <p v-if="vote.type === 'user'" class="pcard__value">{{ vote.value }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else-if="game.state === game.states.FINISHED" class="animated-bg">
                <h1 class="text-white">Spillet er afsluttet.</h1>
            </div>
        </template>

        <span class="mute-audio" @click="toggleMute">
            <svg v-if="muted" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="volume-mute" class="svg-inline--fa fa-volume-mute fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path fill="currentColor" d="M215.03 71.05L126.06 160H24c-13.26 0-24 10.74-24 24v144c0 13.25 10.74 24 24 24h102.06l88.97 88.95c15.03 15.03 40.97 4.47 40.97-16.97V88.02c0-21.46-25.96-31.98-40.97-16.97zM461.64 256l45.64-45.64c6.3-6.3 6.3-16.52 0-22.82l-22.82-22.82c-6.3-6.3-16.52-6.3-22.82 0L416 210.36l-45.64-45.64c-6.3-6.3-16.52-6.3-22.82 0l-22.82 22.82c-6.3 6.3-6.3 16.52 0 22.82L370.36 256l-45.63 45.63c-6.3 6.3-6.3 16.52 0 22.82l22.82 22.82c6.3 6.3 16.52 6.3 22.82 0L416 301.64l45.64 45.64c6.3 6.3 16.52 6.3 22.82 0l22.82-22.82c6.3-6.3 6.3-16.52 0-22.82L461.64 256z"></path>
            </svg>
            <svg v-if="!muted" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="volume-up" class="svg-inline--fa fa-volume-up fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                <path fill="currentColor" d="M215.03 71.05L126.06 160H24c-13.26 0-24 10.74-24 24v144c0 13.25 10.74 24 24 24h102.06l88.97 88.95c15.03 15.03 40.97 4.47 40.97-16.97V88.02c0-21.46-25.96-31.98-40.97-16.97zm233.32-51.08c-11.17-7.33-26.18-4.24-33.51 6.95-7.34 11.17-4.22 26.18 6.95 33.51 66.27 43.49 105.82 116.6 105.82 195.58 0 78.98-39.55 152.09-105.82 195.58-11.17 7.32-14.29 22.34-6.95 33.5 7.04 10.71 21.93 14.56 33.51 6.95C528.27 439.58 576 351.33 576 256S528.27 72.43 448.35 19.97zM480 256c0-63.53-32.06-121.94-85.77-156.24-11.19-7.14-26.03-3.82-33.12 7.46s-3.78 26.21 7.41 33.36C408.27 165.97 432 209.11 432 256s-23.73 90.03-63.48 115.42c-11.19 7.14-14.5 22.07-7.41 33.36 6.51 10.36 21.12 15.14 33.12 7.46C447.94 377.94 480 319.54 480 256zm-141.77-76.87c-11.58-6.33-26.19-2.16-32.61 9.45-6.39 11.61-2.16 26.2 9.45 32.61C327.98 228.28 336 241.63 336 256c0 14.38-8.02 27.72-20.92 34.81-11.61 6.41-15.84 21-9.45 32.61 6.43 11.66 21.05 15.8 32.61 9.45 28.23-15.55 45.77-45 45.77-76.88s-17.54-61.32-45.78-76.86z"></path>
            </svg>
        </span>
        <span class="clear-storage" @click="clearStorage">
            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="trash-alt" class="svg-inline--fa fa-trash-alt fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                <path fill="currentColor" d="M32 464a48 48 0 0 0 48 48h288a48 48 0 0 0 48-48V128H32zm272-256a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zm-96 0a16 16 0 0 1 32 0v224a16 16 0 0 1-32 0zM432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16z"></path>
            </svg>
        </span>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="app.js" charset="utf-8"></script>
</body>

</html>