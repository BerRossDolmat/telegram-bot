<template>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="input-group">
                        <span class="input-group-addon" id="basic-addon1">Message</span>
                        <input type="text" class="form-control" placeholder="Enter text" aria-label="Message" v-model="newMessage" @keyup.enter="sendMessage" aria-describedby="basic-addon1">
                        <button v-on:click="sendMessage">SEND</button>
                    </div>
                    <p>Press Enter to send message</p>

                    <ul>
                        <li v-for="message in messages" >
                            <p>{{ message.text }}</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    const $ = require('jquery');
    export default {
        data() {
            return {
                messages: [],
                newMessage: null
            };
        },

        methods: {
            _addMessage(text) {
                this.messages.push({
                    'text': text,
                });
            },

            sendMessage() {
                let messageText = this.newMessage;
                this.newMessage = '';
                if (!messageText.trim()) {
                    return;
                }
                this._addMessage(messageText);

                $.ajax({
            		url: "/admin_message",
            		type: "POST",
            		data: {message: messageText},
            	});
            }
        }
    }
</script>
