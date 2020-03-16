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
            <div class="auth" v-if="session.auth">
                <div class="vote" v-show="!hasVoted || (joined.length !== votes.length)">
                    <button :disabled="hasVoted" class="btn btn-outline-dark mr-2" :class="{ 'active' : chosenCard === index }" @click="select(card)" v-for="(card, index) in cards" :key="'card-'+index">
                        {{ card.value }}
                    </button>
                    <button class="btn btn-primary" @click="vote" :disabled="hasVoted">Vote!</button>
                </div>
                <div class="look" v-show="hasVoted && (joined.length === votes.length)">
                    Show all votes
                    <button class="btn btn-primary" @click="next">Next!</button>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
        <script src="app.js" charset="utf-8"></script>
    </body>
</html>
