<?php
$websocketMap = [
    '127.0.0.1' => 'ws://localhost:9000',
    '165.227.174.67' => 'ws://planningpoker.rasmusbundsgaard.dk/websocket',
];

$websocketConnection = isset($websocketMap[$_SERVER['SERVER_ADDR']]) ? $websocketMap[$_SERVER['SERVER_ADDR']] : $websocketMap['127.0.0.1']; 
?>
<!DOCTYPE html>
<html lang="da" dir="ltr">
    <head>
        <meta charset="utf-8">
        <title>Planning Poker</title>
        <link rel="stylesheet" href="bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <link rel="stylesheet" href="app.css?v=0f33c1486246ebfd8f019b0d64a89e12">
        <script>
            window.PLANNINGPOKER = {
                websocketUrl: "<?php echo $websocketConnection; ?>",
            };
        </script>
    </head>
    <body>
        <div id="app">
            <audio loop ref="audio">
                <source src="music.mp3" type="audio/mp3">
            </audio>
            <div class="login animated-bg" v-if="!session.auth">
                <div class="d-flex">
                    <select class="form-control mr-2" v-model="session.username" :disabled="availableUsers.length === 0">
                        <option value="">Vælg bruger</option>
                        <option v-for="(user, index) in availableUsers" :key="'option-user-'+index">
                            {{ user }}
                        </option>
                    </select>
                    <button @click="join" class="btn btn-primary">Ok!</button>
                </div>
            </div>
            <div class="waiting animated-bg" v-if="session.auth && pendingSync">
                <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
            </div>
            <div class="play my-3 mx-3" v-if="session.auth && !pendingSync">
                <div class="vote" v-show="(!hasVoted || (joined.length !== votes.length)) && !votesData.length">
                    <div class="cards">
                        <button class="pcard" :disabled="hasVoted" :class="{ 'pcard--active' : chosenCard === index }" @click="select(card)" v-for="(card, index) in cards" :key="'card-'+index">
                            <div class="pcard__inner">
                                <div class="pcard__symbol pcard__symbol--big">{{ card.value }}</div>
                            </div>
                        </button>
                    </div>
                    <button class="btn btn-primary btn-huge snap-bottom" @click="vote" :disabled="hasVoted">Vote!</button>
                </div>
                <div class="showoff" v-show="votesData.length">
                    <div class="votes">
                        <div class="pcard" v-for="(vote, index) in votesData" :key="'vote-'+index">
                            <div class="pcard__inner">
                                <div class="pcard__symbol pcard__symbol--big">
                                    <h5 class="card-title text-uppercase">{{ vote.username }}:</h5>
                                    <p>{{ cards[vote.vote_id].value }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-huge snap-bottom" @click="next">Next!</button>
                </div>
            </div>
            <span class="mute-audio" @click="muteAudio">
                <svg v-if="muted" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="volume-mute" class="svg-inline--fa fa-volume-mute fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M215.03 71.05L126.06 160H24c-13.26 0-24 10.74-24 24v144c0 13.25 10.74 24 24 24h102.06l88.97 88.95c15.03 15.03 40.97 4.47 40.97-16.97V88.02c0-21.46-25.96-31.98-40.97-16.97zM461.64 256l45.64-45.64c6.3-6.3 6.3-16.52 0-22.82l-22.82-22.82c-6.3-6.3-16.52-6.3-22.82 0L416 210.36l-45.64-45.64c-6.3-6.3-16.52-6.3-22.82 0l-22.82 22.82c-6.3 6.3-6.3 16.52 0 22.82L370.36 256l-45.63 45.63c-6.3 6.3-6.3 16.52 0 22.82l22.82 22.82c6.3 6.3 16.52 6.3 22.82 0L416 301.64l45.64 45.64c6.3 6.3 16.52 6.3 22.82 0l22.82-22.82c6.3-6.3 6.3-16.52 0-22.82L461.64 256z"></path></svg>
                <svg v-if="!muted" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="volume-up" class="svg-inline--fa fa-volume-up fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M215.03 71.05L126.06 160H24c-13.26 0-24 10.74-24 24v144c0 13.25 10.74 24 24 24h102.06l88.97 88.95c15.03 15.03 40.97 4.47 40.97-16.97V88.02c0-21.46-25.96-31.98-40.97-16.97zm233.32-51.08c-11.17-7.33-26.18-4.24-33.51 6.95-7.34 11.17-4.22 26.18 6.95 33.51 66.27 43.49 105.82 116.6 105.82 195.58 0 78.98-39.55 152.09-105.82 195.58-11.17 7.32-14.29 22.34-6.95 33.5 7.04 10.71 21.93 14.56 33.51 6.95C528.27 439.58 576 351.33 576 256S528.27 72.43 448.35 19.97zM480 256c0-63.53-32.06-121.94-85.77-156.24-11.19-7.14-26.03-3.82-33.12 7.46s-3.78 26.21 7.41 33.36C408.27 165.97 432 209.11 432 256s-23.73 90.03-63.48 115.42c-11.19 7.14-14.5 22.07-7.41 33.36 6.51 10.36 21.12 15.14 33.12 7.46C447.94 377.94 480 319.54 480 256zm-141.77-76.87c-11.58-6.33-26.19-2.16-32.61 9.45-6.39 11.61-2.16 26.2 9.45 32.61C327.98 228.28 336 241.63 336 256c0 14.38-8.02 27.72-20.92 34.81-11.61 6.41-15.84 21-9.45 32.61 6.43 11.66 21.05 15.8 32.61 9.45 28.23-15.55 45.77-45 45.77-76.88s-17.54-61.32-45.78-76.86z"></path></svg>
            </span>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
        <script src="app.js?v=0f33c1486246ebfd8f019b0d64a89e12" charset="utf-8"></script>
    </body>
</html>
