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
        <link rel="stylesheet" href="app.css">
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
                <div class="vote" v-show="!hasVoted || (joined.length !== votes.length)">
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
                    <!-- <div class="row mb-5">
                        <div class="col-3 col-lg-2 col-xl-2" v-for="(vote, index) in votesData" :key="'vote-'+index">
                            <div class="card p-2">
                                <h5 class="card-title text-uppercase">{{ vote.username }}</h5>
                                <p class="card-text">Voted: <strong>{{ cards[vote.vote_id].value }}</strong></p>
                            </div>
                        </div>
                    </div> -->
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
        </div>
        <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
        <script src="app.js" charset="utf-8"></script>
    </body>
</html>
