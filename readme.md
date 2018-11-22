## Project Summary

| Project Name | Influencer HUB |
| --- | --- |
| Website | http://influencerhub.com/ |
| Git Repository | https://gitlab.com/bodecontagion/influencer-hub |
| Status | Recently Launched |
| Project Location | Melbourne, Australia |
| Due Date | 05 March 2018 |
| Tech Stack | Laravel 5.4, MySQL, Gulp/Sass, Vue.js, Quasar |
| Servers | Linode via Laravel Forge |
| Coding Conventions | [PSR-2](http://www.php-fig.org/psr/psr-2/) |
| 1. | Please use tabs instead of spaces for indentation |
| 2. | Commit with unix line endings (\n) |
| 3. | Will expand on coding conventions a little later |

## How to install

Please switch to npm version 5.2.0 as much as possible as the other 
npm versions might cause an issue described here 
[https://github.com/npm/npm/issues/17858](https://github.com/npm/npm/issues/17858)

1: run `git clone https://gitlab.com/bodecontagion/influencer-hub.git`<br/>
2: run `composer install`<br/>
3: run `npm install`

## Assets Overview

| Servers |                                |
| ------- | ------------------------------ |
| Service | Linode (https://www.linode.com) |
| Staging | Pooled / staging |
| Production | Dedicated / production |
| Account | Bode Contagion |

| Deployments |                                |
| ------- | ------------------------------ |
| Service | Laravel Forge (https://forge.laravel.com) |
| Plan    | Pooled / Individual |
| Account | Bode Contagion |

| Broadcast |                                |
| ------- | ------------------------------ |
| Service | Pusher (https://pusher.com) |
| Plan    | Dedicated / Free |
| Account | app.influencerhub@gmail.com |

| Mail |                                |
| ------- | ------------------------------ |
| Service | SendGrid (https://sendgrid.com) |
| Plan    | Pooled / Agency  |
| Account | Bode Contagion / influencerhub |