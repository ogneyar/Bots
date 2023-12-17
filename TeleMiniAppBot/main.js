
import dotenv from 'dotenv'
dotenv.config()

import { Telegraf, Markup } from 'telegraf'
import { message } from 'telegraf/filters'

const token = process.env.TOKEN

const webAppUrl = "https://ng-tele-mini-app.web.app"

const bot = new Telegraf(token)

bot.command('start', (ctx) => {
    ctx.reply(
		'Добро пожаловать!',
		Markup.keyboard([
			Markup.button.webApp(
				'Отправить сообщение',
				webAppUrl
			)]
		).resize()
    )
})

bot.on(message('photo'), ctx => ctx.reply('👍'))
bot.hears('hi', Telegraf.reply('Hey there'))


bot.launch(console.log('TeleMiniAppBot Run'))

// Enable graceful stop
process.once('SIGINT', () => bot.stop('SIGINT'))
process.once('SIGTERM', () => bot.stop('SIGTERM'))
