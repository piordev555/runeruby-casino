<p align="center">
  <img src="resources/img/misc/win5x_logo_black.png" alt="Win5X Logo">
</p>
<p align="center">
    <img src="https://img.shields.io/static/v1.svg?label=version&message=3.7.0/RR&color=black">
</p>

# Installation

## WebSockets Setup

Install **fork** of `laravel-echo-server`:

```
npm install -g laravel-echo-server-whisper
```

- If NPM repository is down, install it from [https://github.com/RuneRuby-Team/laravel-echo-server](https://github.com/RuneRuby-Team/laravel-echo-server)

### Private Keys & WS Encryption

#### Recaptcha

Recaptcha is required to authorize on the website.

Steps to enable Recaptcha:

1. Create Recaptcha v2 token
2. Create file `/storage/recaptcha.key` and put token in it
3. Run `npm run dev` to activate Recaptcha
4. Set `recaptcha_secret_key` in admin panel settings

#### WebPush

Generate vapid keys: `php artisan webpush:vapid`

#### `.env` file

Copy `.env.example` file and rename it to `.env`, afterwards run `php artisan key:generate`.

Change `APP_URL` to your website URL (Important for webhooks!)

#### Private keys

Our Client <-> Server WS implementation requires RSA public key & server key to prevent access token stealing.

Generation process is simple: run `php artisan win5x:keys`.

If you intend to use BitGo service, backup `BITGO_PASSPHRASE` from `.env` to somewhere so you won't lose access to BitGo wallet by some accident.

*Note: you need to execute `npm run dev` to include RSA public key in js source. If you test website straight after w/o compiling it won't authenticate you to WS server.*

## Scheduler

Configure scheduler:

`crontab -e`

`* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1`

#### WS Server Installation

```
laravel-echo-server init  # When asked, use port 2096 and setup SSL if you want to use it
```

Modify **laravel-echo-server.json**
```$json
"databaseConfig": {
	"redis": {
		"password": "redis password (set "redis" to {} if none)"
	},
	"sqlite": {
		"databasePath": "/database/laravel-echo-server.sqlite"
	},
	"listenWhishper": true,
	"prefixWhishper": "whisper"
},
```

Finally, run `win5x:subscribe`:
```
php artisan win5x:subscribe
```

### Bots

To create fake presence of users, you can start a bot to do that. Navigate to `/admin/bot`, modify settings to suit your needs and click at "Start".

If you wish to **stop** bot spawning, use `php artisan queue:clear`.

*Note: this command clears every job in the queue. After that you must restart multiplayer game queue with `php artisan game:chain all`.*

Bet value is hardcoded to be random from 1$ to 25$ (determined from current `CoinGecko` price), where higher bets are less common.

# Wallet installation

We support two ways to provide wallets on your website. You can configure available currencies in admin panel.

## BitGo

3rd-party Bitcoin wallet provider. This method is quicker to install than local nodes, also you won't have to waste server resources.

**1.** Install BitGoJS:
```
cd BitGoJS/modules/express
npm ci
```

**Note:** We do not recommend installing BitGo Express as root, but if you need to do so, you must run `npm ci --unsafe-perm` for the last step instead of `npm ci`.

### Running BitGo Express

From the express module folder (`modules/express`), run this command:

`npm run start`

You should see BitGo Express start up in the default test environment:

```
BitGo-Express running
Environment: test
Base URI: http://localhost:3080
```

### Running in production

When running BitGo Express against the BitGo production environment using real funds, you should make sure the `NODE_ENV` environment variable is set to `production`. This will turn off some debugging information which could leak information about the system which is running BitGo Express. If an unsafe configuration is detected, BitGo Express will emit a warning upon startup. In a future version of BitGo Express, this will turn into a hard error and BitGo Express will fail to start up.

Additionally, when running against the production env and listening on external interfaces, BitGo Express must be run with TLS enabled by setting the `keyPath` and `crtPath` configuration options, otherwise BitGo Express will error upon startup with the following message:

```
Fatal error: Must enable TLS when running against prod and listening on external interfaces!
Error: Must enable TLS when running against prod and listening on external interfaces!
```

We strongly recommend always enabling TLS when running against the BitGo production environment. However, if you must opt out of this requirement, you may do so by setting the `disableSSL` configuration option. Use at your own risk, as this may allow a man-in-the-middle to access sensitive information as it is sent over the wire in cleartext.

### Webhook

Webhook (`https://<url>/api/bitgoWebhook`) is created automatically for BitGo wallets.

## Native bitcoin nodes

If you wish to use local BTC nodes, then we provide 7 cryptocurrencies.

```$bash
cd nodes/<node folder>/bin
./start.sh
```

Modify credentials in start.sh files.

Full synchronization may take up to 1 week with average of 3 days depending on server internet connection/CPU/etc.

*Do not synchronize more than 2 nodes at same time, your performance will suffer!*

### Ethereum

./start.sh is located in geth folder.

Ethereum lightclient node needs peers to work properly.
You may find them [here](https://gist.github.com/rfikki/e2a8c47f4460668557b1e3ec8bae9c11).

Run ```web3.js``` in project root to process Ethereum payments.
```
cd <your website root>
npm install -g pm2
pm2 start web3.js
```

### TRX

This wallet is working on a remote node. Note: deposits are not instant on it, it will require 1-10 minutes based on your/remote node server load.

### ERC-20

ERC-20 is working on Ethereum protocol, so geth client is used.

You don't have to do anything, if Ethereum is working, then ERC-20 will work too.

## RPC urls

This script uses node RPC to manage wallets.

Put these URLs in admin panel, "Currencies" page.

BTC - `http://user:password@localhost:8445`

BCH - `http://user:password@localhost:8446`

DOGE - `http://user:password@localhost:22555`

LTC - `http://user:password@localhost:9332`

## Remote wallet server

#### Skip this step if you will run nodes and RuneRuby on same server.

It's possible to set up nodes on a separate server.

* Copy node binaries and `web3.js` from main server to wallet server

* Replace RPC urls:

Replace `localhost` in admin panel to your second server ip/domain.

* Modify blocknotify.sh & walletnotify.sh:

In `App\BlockNotify\<System>\walletnotify.<sh/bat>` and `App\WalletNotify\<System>\walletnotify.<sh/bat>` change `localhost` to your wallet server ip/domain.

* Run nodes on second server

## Wallet Auto-setup

You can't change some wallet settings initially. That's because they are auto-generated.

***Auto-setup requires every node to be running (Except ERC-20 and TRX)***

Navigate ```/admin/wallet/autoSetup``` in your browser (authenticated as admin user) when all native nodes are working.

**Wallet backups are located in ```/storage/app```. Save them and remove them from your server to prevent leaks.
Store ETH and TRX addresses/private keys in text file manually.**

## Multiplayer games

First step is to [setup supervisor](https://laravel.com/docs/7.x/queues#supervisor-configuration).

After setup is complete you should start the chain so the game would work infinitely (```php artisan game:chain <game_id>```).

Example:
```
php artisan queue:clear        # Clear queue so unexpected things wouldn't happen
php artisan game:chain all     # Start chain for all games

# Example (debugging)
php artisan game:chain crash   # Start chain for Crash only
```

Current multiplayer games identifiers:

* crash
* baccarat
* slide
* bullvsbear

### `Bull vs Bear` game

Since this game uses `Coinbase Pro API` instead of default provably fair system, we are using NodeJS instance for pulling real-time data.

```
npm install -g pm2
pm2 start bullvsbear.js
```

**Note:** it's still a MultiplayerGame instance. You need to use `php artisan game:chain` command to launch this game!

Don't forget to edit `SERVER_IP` string in your `.env` file.

## Troubleshooting

#### Website and client-side provably fair results are different

Float precision tends to work differently in PHP, so there will be slight float differences.

Difference is very small, but sometimes it will be enough to make result invalid.

To fix this, make this change in ```php.ini```:
```
float_precision = -1
```

#### "Connecting to the server..."

Make sure that WS server (`php artisan win5x:subscribe` with `laravel-echo-server`) are running.

## Post-install

Use this shell script to quickly launch WS server, Laravel queue and multiplayer games:

`./start.sh`

## Notes

* Never run ```php artisan win5x:subscribe``` twice. If you need to restart it, kill previous process, otherwise events will be processed twice - chat will duplicate messages, users could dupe their balance.
* Never run ```php artisan game:chain``` twice. If you need to clear & restart queue chain, run `php artisan queue:clear` before.
* Subscribe command uses cached php classes for games. If you've changed them - restart command so changes could take effect.
* Some currencies are supported by BitGo and some are supported as native. While most of the currencies share user balances, we recommend to pick something once and never change it, because if you remove Bitcoin SV and switch to local nodes completely - your users will lose their funds.
