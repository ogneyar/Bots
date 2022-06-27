const fs = require("fs");
let fetch = require('node-fetch');

let update_id = 0;
let from_id;
let text;
let update;
let result;
let message;
let brk = "null";


module.exports = class Bot {
    
    
    constructor(bot_token, webhook = "null") {

        
        this.uri = `https://api.telegram.org/bot${bot_token}/`;
        this.getMe = `${this.uri}getMe`;
        this.getUpdates = `${this.uri}getUpdates?offset=`;

        if (webhook == "null") {

            this.getWebhookInfo()
                .then(resolve => {
                    if (resolve.result.url != "") {
                        this.deleteWebhook().then(resolve => {
                            if (resolve.ok) this.long_poling(update_id);
                            else console.log("Error deleteWebhook");
                        });
                    }else this.long_poling(update_id);
                });
        
        }else {
        
            this.getWebhookInfo()
                .then(resolve => {
                    if (resolve.result.url == "" || resolve.result.url != webhook) this.setWebhook(webhook);
                });

        }

    }


    call(url) {
        return this.Get(url)
            .then(resolve => JSON.parse(resolve) );
    }

    // close() {
    //     return this.call(`${this.uri}close`);
    // }

    getWebhookInfo() {
        return this.call(`${this.uri}getWebhookInfo`);
    }

    setWebhook(url) {
        return this.call(`${this.uri}setWebhook?url=${url}`);
    }

    deleteWebhook() {
        return this.call(`${this.uri}deleteWebhook`);
    }

    sendMessage(chat_id, text, parse_mode = '', ReplyKeyboardMarkup = '') {
        if (ReplyKeyboardMarkup) ReplyKeyboardMarkup = JSON.stringify(ReplyKeyboardMarkup);
        return this.call(`${this.uri}sendMessage?chat_id=${chat_id}&text=${text}&parse_mode=${parse_mode}&reply_markup=${ReplyKeyboardMarkup}`);
    }


    // функция отправляет GET запрос на URL
    async Get(url) {
        let response = await fetch(encodeURI(url), {
            headers: {
                "User-Agent": "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.105 Safari/537.36 OPR/70.0.3728.106",
                "Accept": "*/*",
                "Connection": "keep-alive"
            }
        });
        return response.text();
    }


    long_poling() {
        let request = this.getUpdates + (update_id + 1);
        // console.log(request);
        this.Get(request).then((resolve, reject) => {
            if (resolve) {
                // console.log(resolve);
                update = JSON.parse(resolve);
                if (!update.ok) {
                    brk = "break";
                    console.log("break");
                }else {
                    result = update.result;
                    for (let i = 0; i < result.length; i++) {
                        update_id = result[i].update_id;
                        message = result[i].message;
                        text = message.text;
                        from_id = message.from.id;

                        let ReplyKeyboardMarkup = {
                            'keyboard':[
                                [
                                    {'text':'КАКАЯ-ТО кнопка тут'}
                                ]
                            ],
                            'resize_keyboard':true
                        }

                        if (text == "/start") {
                            this.sendMessage(from_id, "Приветствую!\n\nНажми кнопку ниже или пришли команду /help", "markdown", ReplyKeyboardMarkup);
                        }else if (text == "/help" || text == "КАКАЯ-ТО кнопка тут") {

                            // this.button(from_id); 
                            this.sendMessage(from_id, 'реализация работы МЕНЯ - пока хромает, но это времено', "markdown");

                        }else {
                            console.log(text);
                            this.sendMessage(from_id, `*Не понимаю(*`, "markdown");
                        }

                        // console.log(result[i]);

                    }
                }
                // async_func().then((res,rej) => {
                new Promise((reso, reje) => {
                        setTimeout(() => {
                            reso("result");
                        }, 1000);
                }).then((res,rej) => {
                    if (res) {

                        if (brk != "break") {
                            this.long_poling();
                            console.log("last update id: ", update_id);
                        }else {
                            this.Get(this.getUpdates + (update_id + 1)).then((resolve, reject) => {
                                if (resolve) {
                                    console.log("exit");
                                }
                            });
                            // sendMessage(from_id, `*${text}*`, "markdown");
                        }

                    }else if (rej) {
                        console.log("rej");
                    }else {
                        console.log("else");
                    }
                });
        
            }else if (reject) {
                console.log("reject");
            }else {
                console.log("else");
            }
        })
        .catch(e => console.log("catch error: ", e));
    }



}

