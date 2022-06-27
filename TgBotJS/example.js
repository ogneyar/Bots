let Bot = require("./bot");
require('dotenv').config();

let bot_token = process.env.BOT_TOKEN;
let bot_webhook = process.env.BOT_WEBHOOK;

// Запуск бота так, где webhook - адрес куда будут приходить сообщения от бота
new Bot(bot_token, bot_webhook); 

// или так, используя long_poling
// new Bot(bot_token); 