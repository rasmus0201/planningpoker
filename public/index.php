<!DOCTYPE html>
<html lang="da" dir="ltr">
    <head>
        <meta charset="utf-8">
        <title>Planning Poker</title>
        <link rel="stylesheet" href="bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <link rel="stylesheet" href="app.css">
    </head>
    <body>
        <div id="app">
            <div class="login" v-if="!session.auth">
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
            <div class="auth my-3 mx-3" v-if="session.auth">
                <div class="vote" v-show="!hasVoted || (joined.length !== votes.length)">
                    <button :disabled="hasVoted" class="btn btn-outline-dark mr-2" :class="{ 'active' : chosenCard === index }" @click="select(card)" v-for="(card, index) in cards" :key="'card-'+index">
                        {{ card.value }}
                    </button>
                    <button class="btn btn-primary" @click="vote" :disabled="hasVoted">Vote!</button>
                </div>
                <div class="showoff" v-show="votesData.length">
                    <div class="row mb-5">
                        <div class="col-3 col-lg-2 col-xl-2" v-for="(vote, index) in votesData" :key="'vote-'+index">
                            <div class="card p-2">
                                <h5 class="card-title text-uppercase">{{ vote.username }}</h5>
                                <p class="card-text">Voted: <strong>{{ cards[vote.vote_id].value }}</strong></p>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary snap-bottom" @click="next" :disabled="session.round_id != sync_round">Next!</button>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
        <script src="app.js" charset="utf-8"></script>
    </body>
</html>
