<template>
    <div class="trello">

        <modal name="my-first-modal" class="modal">
            <div>
                <div class="vm--modal--head">
                    <input class="vm--modal--input" placeholder="Title" type="text" v-model="name">
                    <button @click="delete_card" class="button button__default">Delete</button>
                </div>
                <span class="vm--modal--list">in the list <span class="vm--modal--list__list" v-text="list"></span></span>
            </div>
            <textarea class="vm--modal--textarea" placeholder="Description" v-model="description"></textarea>
            <div class="vm--modal--buttons">
                <button @click="cancel" class="button button__default">Cancel</button>
                <button @click="save" class="button button__info">Save</button>
            </div>
        </modal>

        <div class="columns">

            <div v-for="list in lists" :key="list.id" class="column">
                <div class="column--head">
                    <span class="column--head__name">{{list.name}}</span>
                    <button @click="delete_list(list.id)" class="column--head__button button button__default">Delete</button>
                </div>
                <draggable :list="list.cards" group="tasks" draggable=".column__card" @change="log($event, list.id)" style="min-height: 39px;">
                    <div class="column__card" @click="show_card(list.id,card.id)" v-for="card in list.cards" :key="card.id">
                        {{card.name}}
                    </div>
                </draggable>
                <div class="column--add" @click="add_card(list.id)">Add card</div>
            </div>
            <div class="column">
                <input class="vm--modal--input" placeholder="Add new list" type="text" v-model="title_new">
                <button @click="save_list" v-if="title_new !== ''" class="button button__info">Save</button>
            </div>
        </div>
        <div class="trello--buttons">
            <button @click="download" class="button button__info trello--buttons--download">Download</button>
        </div>


    </div>
</template>

<script>  
    import draggable from 'vuedraggable'
    export default {
        name: 'Trello',
        data(){
            return {
                list_id: 0,
                card_id: 0,
                title_new: '',
                name: '',
                description: '',
                list: '',
                lists: []
            }
        },
        mounted() {
            this.$http.get('/list').then(response => { 
                this.lists = response.body
            }, response => { 
                alert('Error'); 
            }); 
        },
        computed: {
        },
        methods: {
            download(){
                this.$http.get('/download').then(response => {
                    console.log(response)
                }, response => { 
                    alert('Error'); 
                }); 
            },
            delete_card(){
                if(confirm('Are you sure you want to delete this card?')){
                    this.$http.delete('/card/' + this.card_id).then(response => { 
                        let index = 0
                        this.lists.forEach((list,list_index) => {
                            if(list.id === this.list_id){
                                list.cards.forEach((card,card_index) => {
                                    if(card.id === this.card_id){
                                        this.lists[list_index].cards.splice(card_index,1)
                                    }
                                })
                            }
                        })
                        this.$modal.hide('my-first-modal');
                    }, response => { 
                        alert('Error'); 
                    }); 
                }
            },
            delete_list(list_id){
                if(confirm('Are you sure you want to delete this list?')){
                    this.$http.delete('/list/' + list_id).then(response => { 
                        let index = 0
                        this.lists.forEach((list,list_index) => {
                            if(list.id === list_id){
                                index = list_index
                            }
                        })
                        this.lists.splice(index, 1)
                    }, response => { 
                        alert('Error'); 
                    }); 
                }
            },
            save_list(){
                this.$http.post('/list',{
                    name: this.title_new
                }).then(response => { 
                    this.lists.push({
                        id: response.data.id,
                        name: this.title_new,
                        cards: []
                    })
                    this.title_new = ''
                }, response => { 
                    alert('Error'); 
                }); 
            },
            save(){
                //Get the last order id 
                let last_card_order = -1
                this.lists.forEach((list,list_index) => {
                    if(list.id === this.list_id){
                        list.cards.forEach(card => {
                            if(card.order > last_card_order)
                                last_card_order = card.order
                        })
                    }
                })
                if(this.card_id === 0){
                    this.$http.post('/card',{
                        name: this.name,
                        description: this.description,
                        list_id: this.list_id,
                        order: last_card_order + 1
                    }).then(response => { 
                        this.lists.forEach((list,list_index) => {
                            if(list.id === this.list_id){
                                list.cards.push(response.data)
                            }
                        })
                    }, response => { 
                        if(response.body?.errors){
                            for (var k in response.body?.errors){
                                if (response.body?.errors.hasOwnProperty(k)) {
                                    alert(response.body?.errors[k]);
                                }
                            }
                        }
                    }); 
                }else{
                    this.$http.patch('/card/' + this.card_id,{
                        name: this.name,
                        description: this.description,
                        list_id: this.list_id
                    }).then(response => { 
                        this.lists.forEach((list,list_index) => {
                            if(list.id === this.list_id){
                                this.lists[list_index].cards.forEach((card, card_index) => {
                                    if(card.id === this.card_id){
                                        this.lists[list_index].cards[card_index].name = this.name
                                        this.lists[list_index].cards[card_index].description = this.description
                                    }
                                })
                            }
                        })
                    }, response => { 
                        if(response.body?.errors){
                            for (var k in response.body?.errors){
                                if (response.body?.errors.hasOwnProperty(k)) {
                                    alert(response.body?.errors[k]);
                                }
                            }
                        }
                    }); 
                }
                this.$modal.hide('my-first-modal');
            },
            cancel(){
                this.$modal.hide('my-first-modal');
            },
            add_card(list_id){
                this.list_id = list_id
                this.list = ''
                this.card_id = 0
                this.name = ''
                this.description = ''
                this.$modal.show('my-first-modal');
            },
            show_card(list_id, card_id){
                this.lists.forEach(list => {
                    if(list.id === list_id){
                        list.cards.forEach(card => {
                            if(card.id === card_id){
                                this.list_id = list.id
                                this.list = list.name
                                this.card_id = card.id
                                this.name = card.name
                                this.description = card?.description
                            }
                        })
                    }
                })
                this.$modal.show('my-first-modal');
            },
            log: function(evt, list_id) {
                window.console.log(evt);
                window.console.log(list_id);

                if(evt.moved){
                    this.$http.patch('/card/' + evt.moved.element.id,{
                        name: evt.moved.element.name,
                        description: evt.moved.element.description,
                        list_id: evt.moved.element.list_id,
                        order: evt.moved.newIndex
                    }).then(response => { 
                    }, response => { 
                        if(response.body?.errors){
                            for (var k in response.body?.errors){
                                if (response.body?.errors.hasOwnProperty(k)) {
                                    alert(response.body?.errors[k]);
                                }
                            }
                        }
                    }); 
                }

                if(evt.added){
                    this.$http.patch('/card/' + evt.added.element.id,{
                        name: evt.added.element.name,
                        description: evt.added.element.description,
                        list_id: list_id,
                        order: evt.added.newIndex
                    }).then(response => { 
                    }, response => { 
                        if(response.body?.errors){
                            for (var k in response.body?.errors){
                                if (response.body?.errors.hasOwnProperty(k)) {
                                    alert(response.body?.errors[k]);
                                }
                            }
                        }
                    }); 
                }


            },
            addTask(){

            }
        },
        components: {
            draggable,
        },
    }
</script>

<style lang="scss">

.button{
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 5px;
    background: whitesmoke;
    cursor: pointer;
    &__default{
        &:hover{
            background: rgb(199, 199, 199);
        }
    }
    &__info{
        background: rgb(0, 145, 255);
        color: white;  
        &:hover{
            background: rgb(0, 115, 203); 
        }
    }
    &__delete{
        background: rgb(255, 63, 63);
        color: white;  
        &:hover{
            background: rgb(157, 0, 0); 
        }
    }
}
.trello{
    background: #f3f3f3;
    &--buttons{
        padding: 10px;
        &--download{
            margin: 5px;
        }
    }
    .columns{
        padding: 10px;
        font-family: Arial, Helvetica, sans-serif;
        display: flex;
        flex-direction: row;
        overflow-y: scroll;
        .column{
            background: white;
            border-radius: 5px;
            padding: 5px;
            margin: 5px;
            cursor: pointer;
            flex-basis: 300px;
            min-width: 200px;
            &--head{
                padding: 5px;
                font-weight: 700;
                display: flex;
                justify-content: space-between;
                &--name{
                    
                }
                &__button{
                    padding: 2px 5px;
                }
            }
            &__card{
                background: whitesmoke;
                padding: 5px;
                margin: 5px;
                border-radius: 5px;
                box-shadow: 0 1px 0 #091e4240;
                &__head{

                }
            }
            &--add{
                background: whitesmoke;
                border-radius: 5px;
                padding: 5px;
                margin: 5px;
                margin-top: 15px;
                text-align: center;
                color: gray;
            }
        }
    }
}
.modal{
    .vm{
        &--modal{
            padding: 10px;
            overflow-y: scroll;
            height: 400px;
            &--input{
                border-radius: 5px;
                font-size: 16px;
                padding: 10px;
                min-width: 300px;
                margin-bottom: 10px;
                width: 100%;
                font-weight: 700;
                &:focus{
                    border: 1px #c4c4c4 solid;
                }
            }
            &--list{
                font-size: 14px;
                margin-top: -10px;
                display: block;
                margin-bottom: 16px;
                margin-left: 10px;
                &__list{
                    text-decoration: underline;
                }
            }
            &--textarea{
                border: 1px #c4c4c4 solid;
                border-radius: 5px;
                font-size: 16px;
                padding: 10px;
                min-width: 300px;
                margin-bottom: 10px;
                font-family: Arial, Helvetica, sans-serif;
                width: 100%;
                height: 150px;
            }
            &--buttons{
                display: flex;
                justify-content: space-between;
            }
            &--head{
                display: flex;
                justify-content: space-between;
                button{
                    padding: 3px 5px;
                    height: 30px;
                    margin-top: 3px;
                }
            }
        }
    }
}
input{
    border-radius: 5px;
    font-size: 16px;
    padding: 10px;
    margin-bottom: 10px;
    width: 100%;
    font-weight: 700;
    &:focus{
        border: 1px #c4c4c4 solid;
    }
}
</style>
