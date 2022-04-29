<template>
    <div>
        <div class="limbo_canvas">
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAPYAAABABAMAAADbr5eTAAAAD1BMVEX///8AAAD///////////9cEmbOAAAABXRSTlMTAA4JBDkaubkAAAGqSURBVFjDxdjRjeIwEIDh/xwKwLELcBIKSBYKiDn6r+mk1Z5Gy2YzIszI/yM8fJp4YiGIL/T4GCGMt2jTC3Yu/G+KSsb2HaSwRCUrW2hJwY1soXXcw05gjuu2rNlzoUYlE3tlqy4qWdiJ7YaoZGDPbBfiU/Z2AvvBdVvG1gf3sDN4DK7bsuTaqrvY7FVd7Z69TlGyt2cAfdtcbParjnav2H+iZG2vAPqmu9gFpSgZ2xmtJUq2dg9OB67bMzgduG6jFrzshF6N0cU+ozc42TOScqWb2fk6lnG6RUDvdh0hXKuNnQoHulnYiWNd3rczRxvetmcOV4/Y+aOEaVGeuF53wL7LgcnY3k8dob/wBP6Diy0c06PwVstrdsaw7jV7Rclg1fNj005sZLttfwsw1Z/2imnh938tlmc7Y9zlc8ywPNGCi93j1GXjmqzfbfnKB7+D1H238Ugecf59E0n4FeQFlo8kehw7xbTzCnLGszrv3HvMeNbxo8XR1i/8x9fPSgotmj7tRnUNbYaGdmhoMzS0u4Y2lUKrhob2iZlWday0KnCmXZl2xXuhTd0/i6cAPsoSejMAAAAASUVORK5CYII=" class="cloud cloud-r" alt>
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMAAAAAwBAMAAAC1YGgtAAAAD1BMVEX///8AAAD///////////9cEmbOAAAABXRSTlMTAA4IBCABiPgAAAEzSURBVFjDtdVhjoIwEEDhx+ABLO0BBtYDWPUAVPf+Z9qYuAGkRIHp95+8DNApbpNHB3TX5D7aFPDKy08qEfAMJNoHAhPRPKBMJeNAw5vaNhCYaU0DDXPJMqDMHQwDDTmGgTM5rVkgkFWbBY7kJauAktcaBTwLaqPAkSVGAWVJNAl4FlUmgZ5FtUmAZWIRaBjkTkJImwPhonzQPhQkbgt45VunLQHPCnF9ICgryOuh3+8DZ1ZpX+9Ubl8GPCsl50ffA+sBQC7jYbAbYE7SW8BfpLvN1sMOh2ngztPP7I7cIY0D9//scHqlU3apRgE/PY1BMSCjgE4Xl2IikluV9fBz7lWRvQ1jj5EDwwBFyDMQHtcrA/OAV0pyQSnK9ZTlKErwFCUcKaqmp6gapaiqdCBSlJzcHzwgmJkN1iU1AAAAAElFTkSuQmCC" class="cloud cloud-d" alt>
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAPYAAABABAMAAADbr5eTAAAAD1BMVEX///8AAAD///////////9cEmbOAAAABXRSTlMTAA4JBDkaubkAAAGqSURBVFjDxdjRjeIwEIDh/xwKwLELcBIKSBYKiDn6r+mk1Z5Gy2YzIszI/yM8fJp4YiGIL/T4GCGMt2jTC3Yu/G+KSsb2HaSwRCUrW2hJwY1soXXcw05gjuu2rNlzoUYlE3tlqy4qWdiJ7YaoZGDPbBfiU/Z2AvvBdVvG1gf3sDN4DK7bsuTaqrvY7FVd7Z69TlGyt2cAfdtcbParjnav2H+iZG2vAPqmu9gFpSgZ2xmtJUq2dg9OB67bMzgduG6jFrzshF6N0cU+ozc42TOScqWb2fk6lnG6RUDvdh0hXKuNnQoHulnYiWNd3rczRxvetmcOV4/Y+aOEaVGeuF53wL7LgcnY3k8dob/wBP6Diy0c06PwVstrdsaw7jV7Rclg1fNj005sZLttfwsw1Z/2imnh938tlmc7Y9zlc8ywPNGCi93j1GXjmqzfbfnKB7+D1H238Ugecf59E0n4FeQFlo8kehw7xbTzCnLGszrv3HvMeNbxo8XR1i/8x9fPSgotmj7tRnUNbYaGdmhoMzS0u4Y2lUKrhob2iZlWday0KnCmXZl2xXuhTd0/i6cAPsoSejMAAAAASUVORK5CYII=" class="cloud cloud-v" alt>
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMAAAAAwBAMAAAC1YGgtAAAAD1BMVEX///8AAAD///////////9cEmbOAAAABXRSTlMTAA4IBCABiPgAAAEzSURBVFjDtdVhjoIwEEDhx+ABLO0BBtYDWPUAVPf+Z9qYuAGkRIHp95+8DNApbpNHB3TX5D7aFPDKy08qEfAMJNoHAhPRPKBMJeNAw5vaNhCYaU0DDXPJMqDMHQwDDTmGgTM5rVkgkFWbBY7kJauAktcaBTwLaqPAkSVGAWVJNAl4FlUmgZ5FtUmAZWIRaBjkTkJImwPhonzQPhQkbgt45VunLQHPCnF9ICgryOuh3+8DZ1ZpX+9Ubl8GPCsl50ffA+sBQC7jYbAbYE7SW8BfpLvN1sMOh2ngztPP7I7cIY0D9//scHqlU3apRgE/PY1BMSCjgE4Xl2IikluV9fBz7lWRvQ1jj5EDwwBFyDMQHtcrA/OAV0pyQSnK9ZTlKErwFCUcKaqmp6gapaiqdCBSlJzcHzwgmJkN1iU1AAAAAElFTkSuQmCC" class="cloud cloud-g" alt>
            <img src="/img/misc/mountain.png" alt class="limbo-bg">
            <div :class="`bg-star show_${star}`">
                <div class="l-star e-r"></div>
                <div class="l-star s-p"></div>
                <div class="l-star r-p"></div>
            </div>
            <div class="game-rocket notranslate">
                <div class="rocket-number">
                    <span class="rocket_payout">x{{ payout.toFixed(2) }}</span>
                    <div class="rocket-boom"></div>
                </div>
                <div class="rocket-wrap fire">
                    <div class="rocket-img">
                        <img src="/img/misc/rocket.png" alt>
                    </div>
                    <div class="rocket-fire"></div>
                </div>
            </div>
        </div>
        <div class="limbo-footer">
            <div class="row">
                <div class="col-6 pr-2">
                    <div>{{ $t('general.target_payout') }}</div>
                    <input type="number" v-model="target_payout" step=".01" :placeholder="$t('general.target_payout')">
                </div>
                <div class="col-6 pl-2">
                    <div>{{ $t('general.win_chance') }}</div>
                    <input type="number" v-model="win_chance" value="50.00" step=".01" :placeholder="$t('general.win_chance')">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Bus from '../../../bus';

    export default {
        data() {
            return {
                target_payout: (2).toFixed(8),
                win_chance: (50).toFixed(8),

                star: 0,
                payout: 1
            }
        },
        created() {
            setInterval(() => {
                this.star++;
                if(this.star >= 3) this.star = 0;
            }, 10000);
        },
        watch: {
            target_payout(value, oldValue) {
                if(isNaN(value) || value < 1.01 || value > 1000000) return this.target_payout = oldValue;
                this.win_chance = parseFloat(this.calculate(value).toFixed(8));
            },
            win_chance(value, oldValue) {
                if(isNaN(value) || value < 0.000099 || value > 98) return this.win_chance = oldValue;
                this.target_payout = parseFloat(this.calculate(value).toFixed(8));
            }
        },
        methods: {
            callback(response) {
                let win = response.game.win, delay = 200;

                this.playSound('/sounds/flying.mp3');

                $('.rocket-wrap').addClass('flying');

                $('.rocket_payout').attr('class', 'rocket_payout');

                $('.rocket-wrap').addClass('flying');

                setTimeout(() => {
                    this.payout = response.game.data.number;
                    $('.rocket-wrap, .rocket-boom').addClass('boom');

                    this.playSound('/sounds/boom.mp3');

                    setTimeout(() => {
                        this.playSound(`/sounds/${win ? 'guessed' : 'lose'}.mp3`);
                        this.updateGameInstance((i) => i.playTimeout = false);

                        $('.rocket_payout').toggleClass('text-danger', !win).toggleClass('text-success', win);

                        Bus.$emit('limbo:history:addEntry', { html: `<div class="text-${win ? 'success' : 'danger'}">${response.server_seed.result[0].toFixed(2)}</div>` });

                        $('.rocket-wrap').removeClass('flying').removeClass('boom');
                        $('.rocket-boom').removeClass('boom');
                    }, delay * 2);
                }, delay);
            },
            calculate(value) {
                return (1000000 / value) / 10000;
            },
            getClientData() {
                return {
                    target_payout: this.target_payout
                }
            },
            getSidebarComponents() {
                return [
                    { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                    { name: 'wager-classic' },
                    { name: 'auto-bets' },
                    { name: 'play' },
                    { name: 'footer', data: { buttons: ['help', 'sound', 'stats'] } },
                    { name: 'history', data: { scrollable: true } }
                ];
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .game-limbo {
        .bg-star {
            height: 9.375rem;
            background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABJcAAAIyCAMAAABB1tu9AAAAb1BMVEUAAAD///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////8v0wLRAAAAJHRSTlMAHAcOAQRLGwsZUAIqJxIWFBAD7oAz9EQ+HryHLHFb3aSxkuEQa2mjAAAGxUlEQVR42uzdAU7CUAwG4L4NtrcxENBERIMYd/8z6g0UE0mXfN8p2uZvG79S+/0qABLZldIEcLPjx+Ml+Bd1UC/BX2zn+RwAiXzXS2Nwq0M5BEAmfekDIJN2aANguWq7CYBE1sU0CshlXYquD8ilqXJMsACC0EAyr/M1ADL5nOdjACRyeX8KAAAAAAAAAAAAgGVprJwCubyNpwDI5HkcLbIBqQynbQCwFHXnjhSQyqovQwBk8lD2AZDJRooCAAAAAAAAAAAAAAAAAAAAAAAAAOCepm4KgEy6sQuATKbO8zqAH72crzUAvti7oxSEYSCKohNjoSIIGqyoRKFk/2t0EVKYwjmLeJ/3JfIe4xMAiTyW9gyATKrvbwAAAAAAAAAAAAAAAAAAAHbt0vUigVxeo2l6AKnYJSCbU78HAAAAAAAAAAAAAADbuAVAKutYAyCTZbQAyKR/ewAAAAAAAAAAAAAAAAAA/zjW6zkAEplLqQGQyFTKHACZTIcAAAAAAAB+7N1JDsIwDAXQJFSFho6rhPtflBNUYoGKJd67QwZblj8AAAAAAAAAAAAAAABAcHtfEkAkvVYbDeF6x9gcvTNLbQIz4Hpzra/EieJagh+4te6/BN9VtnVIAIGsOXvugVAeedICAGJ5KuMAAAD4D8d8SwCBDGNtCSCSVnsCiKQY/QIAAAAAAAAAAAAAAAAAAAAAAAAAAAD4RLlvorOBUNZs3zwQS5l2mf4AAAAAAPBm315xAIShAAg+wj+EhCAQFYDo/c+IBotoxcwxNlkAAAAAgF+WtQ+Amtw5BZTU+Xv5uPKxB5TTz6PBl7chnQEFTU3TBkBFlm3TOAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAICHPTg2ARAGAgD4hYp5SJCAYPZf1BWs5Iu7AwAAAAAAAAAAAAAAAAAAAAAAAP41+hkAhexHPgFQyDzyDoBKzrUHAAAAAAB8t60+A6CQltkDoJArcwVAJaMFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAwMve3aQgCIZhFH00v0qDflA0qGH7X2NLaBS8yDmruLMLAAAAAAAAAAAAAOxFG/sAVPKctgBUsk3HIQCFPN7XAAAAAAAAAAAAAAAAP73WJQCVrJ85AJUss14CAAAAAAAAAAD4n8HuAqilP40tAIUcuu4cgELa7X4JAAAAAAAAAAAAAAAAAAAAAADAl906tgEQhgEAllQdYClDqy6s/H8jPyAhZbCvMAAAAADw3b1XAFSynxkAlazpSwAAAL/prQdAJSOvAKjkzBQmoJQ2jgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOBl145OIARiIIAmqwsugnr2X+z1cB9nXN5rIRBmYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAftHXAKhkyeExzehu7sprXZk9mM468gh4qXYtwXz2zDMAKtk+PR61n6MFQCEtNUmgFnkJAAD+o9sMALX0zC0ACtkyrZmAUtbluAMAAAAAAAAAAAAAAAAAAAAAAAAAvuzdS2rDUAwFUMkfFNPSxAbPuv91dthRC4GSyvY5ixDCvk8XAAAAAABAGw5wKkPmGuc2ln5EOJS5Tt+2XFkBHMk2x8nZlwAAAAAAAAAAAKCFVaQP6GXKXAKgk7XO/vgMAAAAgM6mKQA6mZfl9NeRgGO5Zco/Aa1s97t9CQAAAAAAAAAAAAAA4Nu2uuoI/GKap3ixNXMIuIjcK3jSRz7ixcYsz1O5jH3fgyctWfFqg7HEddSuJedpt7uPPcDFzW8B0Mktly0AGvEzDuhmfqxKP/g3YzmhDvQaLJXCRMAfqyz7EtCKwQJAc4PokRA79DLmYjD9ZPx8F5yHL/buIAdhEIgCKKglxkKpCxYmvf85PUEXLGiIvneIyc8kM/96W4wywZl6HO8AXG3ZbBxPLcIkAAxRyx4AZlJKCQAz2eUlAAAAAAAAAADgH9yq6zZgLjVGBUk9Ptkgh8HusSoi6fBszZUQjLYaSz3W3GoAmMnDEzwAAAAAAAAAAAAAAAAAAAAAAAAAAPhJOeUAMJP0SgFgJjnptgEAAAAAAAAAAAAAAAAAAAD4tnfHNgDCQBAEP3KATGTLCSn910gLT2DpQTNVXLQHAAAAAAAAAAAAAAAAAAAAAAAAkHStGST11gPYbt0jSGpHC2C7OeyltN7OAAAAAAAAAAAAAAAAAAAAAAAAAAAAoDz5faAa+X2gmvf5fXsN+Dd7Daimu0uCD3kAQREZ5qEBz3EAAAAASUVORK5CYII=) no-repeat 20px -20px;
            background-size: 100% auto;
        }

        @keyframes star-fall {
            0% {
                opacity: 0;
                -webkit-transform: scale(0.5) translate(0);
                transform: scale(0.5) translate(0);
            }

            50% {
                opacity: 1;
                -webkit-transform: translate(-200px, 200px);
                transform: translate(-200px, 200px);
            }

            to {
                opacity: 0;
                -webkit-transform: scale(1.2) translate(-300px, 300px);
                transform: scale(1.2) translate(-300px, 300px);
            }
        }

        .bg-star.show_1 .e-r {
            -webkit-animation: star-fall 5s linear;
            animation: star-fall 5s linear;
        }

        .bg-star.show_2 .s-p {
            -webkit-animation: star-fall 8s linear;
            animation: star-fall 8s linear;
        }

        .bg-star.show_3 .r-p {
            -webkit-animation: star-fall 7s linear;
            animation: star-fall 7s linear;
        }

        .bg-star .l-star {
            display: block;
            opacity: 0;
            position: absolute;
            left: 90%;
            width: 1px;
            background: transparent;
        }

        .bg-star .l-star:after {
            content: " ";
            display: block;
            border: 1px solid #fff;
            border-width: 0 20px 2px 50px;
            left: 5px;
            -webkit-transform: rotate(-45deg);
            transform: rotate(-45deg);
            border-color: transparent transparent transparent #eee;
        }

        .bg-star .l-star.s-p {
            left: 30%;
        }

        .bg-star .l-star.s-p:after {
            border-width: 0 10px 2px 25px;
        }

        .bg-star .l-star.r-p {
            left: 45%;
        }

        .bg-star .l-star.r-p:after {
            border-width: 0 15px 2px 30px;
        }

        .game-rocket {
            position: absolute;
            width: 18.75rem;
            left: 50%;
            margin-left: -9.375rem;
            top: 0;
            bottom: 0;
        }

        .game-rocket .rocket-number {
            text-align: center;
            margin-top: 1.25rem;
            -webkit-transition: all 1s;
            transition: all 1s;
            position: relative;
            font-size: 4.6875rem;
            white-space: nowrap;
            color: white;
            font-family: 'Roboto', sans-serif;
        }

        @keyframes boom {
            0% {
                opacity: 0;
            }
            48% {
                background-position: 0 0;
                opacity: 0.5;
            }
            52% {
                background-position: 0 -15.6875rem;
            }
            56% {
                background-position: 0 -31.375rem;
            }
            60% {
                background-position: 0 -47.0625rem;
            }
            64% {
                background-position: 0 -62.75rem;
            }
            68% {
                background-position: 0 -78.4375rem;
            }
            72% {
                background-position: 0 -94.125rem;
            }
            76% {
                background-position: 0 -109.8125rem;
                opacity: 1;
            }
            80% {
                background-position: 0 -125.5rem;
            }
            84% {
                background-position: 0 -141.1875rem;
            }
            88% {
                background-position: 0 -156.875rem;
            }
            92% {
                background-position: 0 -172.5625rem;
            }
            96% {
                background-position: 0 -188.25rem;
            }
            to {
                background-position: 0 -203.9375rem;
                opacity: 0;
            }
        }

        .rocket_payout {
            transition: color 0.3s ease;
        }

        .game-rocket .rocket-number .rocket-boom {
            width: 16.25rem;
            height: 15.0625rem;
            background: url(/img/misc/boom.png) no-repeat;
            background-size: 100% auto;
            opacity: 0;
            background-position: 0 0;
            top: 50%;
            position: absolute;
            left: 50%;
            margin-left: -8.125rem;
            margin-top: -7.5rem;
            -webkit-transform: scale(1.5);
            transform: scale(1.5);
        }

        .game-rocket .rocket-number .rocket-boom.boom {
            -webkit-animation: boom 0.4s steps(1);
            animation: boom 0.4s steps(1);
        }

        .game-rocket .rocket-wrap {
            width: 6.25rem;
            margin: 0 auto;
            opacity: 1;
            position: absolute;
            bottom: 0;
            left: 50%;
            margin-left: -3.125rem;
        }
        .game-rocket .rocket-wrap .rocket-img {
            margin: 0 auto;
            height: 10.3125rem;
            position: relative;
            top: 3.125rem;
        }

        @keyframes rocketanimate {
            0% {
                left: 0.3125rem;
                top: 0.1875rem;
            }
            10% {
                left: 0.1875rem;
                top: 0.25rem;
            }
            20% {
                left: -0.0625rem;
                top: 0.0625rem;
            }
            30% {
                left: -0.1875rem;
                top: 0.125rem;
            }
            40% {
                left: 0.25rem;
                top: 0.3125rem;
            }
            50% {
                left: -0.125rem;
                top: 0;
            }
            60% {
                left: 0.1875rem;
                top: 0.125rem;
            }
            70% {
                left: 0.0625rem;
                top: 0.25rem;
            }
            80% {
                left: -0.0625rem;
                top: 0.0625rem;
            }
            90% {
                left: -0.125rem;
                top: 0.1875rem;
            }
            to {
                left: 0.1875rem;
                top: 0.3125rem;
            }
        }

        .game-rocket .rocket-wrap .rocket-img img {
            position: absolute;
            width: 100%;
            top: 0;
            left: 0;
        }


        @keyframes fireanimate {
            0% {
                background-position: 0 0;
            }
            5% {
                background-position: 0 -6.4375rem;
            }
            10% {
                background-position: 0 -12.875rem;
            }
            15% {
                background-position: 0 -19.3125rem;
            }
            20% {
                background-position: 0 -25.75rem;
            }
            25% {
                background-position: 0 -32.1875rem;
            }
            30% {
                background-position: 0 -38.625rem;
            }
            35% {
                background-position: 0 -45.0625rem;
            }
            40% {
                background-position: 0 -51.5rem;
            }
            45% {
                background-position: 0 -57.9375rem;
            }
            50% {
                background-position: 0 -64.375rem;
            }
            55% {
                background-position: 0 -70.8125rem;
            }
            60% {
                background-position: 0 -64.375rem;
            }
            65% {
                background-position: 0 -57.9375rem;
            }
            70% {
                background-position: 0 -51.5rem;
            }
            75% {
                background-position: 0 -45.0625rem;
            }
            80% {
                background-position: 0 -38.625rem;
            }
            85% {
                background-position: 0 -32.1875rem;
            }
            90% {
                background-position: 0 -25.75rem;
            }
            95% {
                background-position: 0 -19.3125rem;
            }
            98% {
                background-position: 0 -12.875rem;
            }
            to {
                background-position: 0 -6.4375rem;
            }
        }

        .game-rocket .rocket-wrap .rocket-fire {
            margin: -3.75rem auto 0;
            opacity: 0;
            width: 5.0625rem;
            height: 6rem;
            background: url(/img/misc/fire.png) no-repeat;
            background-size: 100% auto;
            background-position: 0 0;
        }

        .game-rocket .rocket-wrap.fire .rocket-img {
            -webkit-transition: top 0.3s;
            transition: top 0.3s;
            top: 0;
        }

        .game-rocket .rocket-wrap.fire .rocket-img img {
            -webkit-animation: rocketanimate 1s linear infinite;
            animation: rocketanimate 1s linear infinite;
        }

        .game-rocket .rocket-wrap.fire .rocket-fire {
            opacity: 1;
            -webkit-animation: fireanimate 0.7s steps(1) infinite;
            animation: fireanimate 0.7s steps(1) infinite;
        }

        .game-rocket .rocket-wrap.flying {
            bottom: 30%;
            -webkit-transition: bottom 0.2s ease-in;
            transition: bottom 0.2s ease-in;
        }

        .game-rocket .rocket-wrap.flying .rocket-img {
            top: 0;
        }

        .game-rocket .rocket-wrap.flying .rocket-fire {
            opacity: 1;
            -webkit-animation: fireanimate 0.7s steps(1) infinite;
            animation: fireanimate 0.7s steps(1) infinite;
        }

        .game-rocket .rocket-wrap.boom {
            -webkit-transition: all 0.2s;
            transition: all 0.2s;
            -webkit-transform: translateY(-50%);
            transform: translateY(-50%);
            opacity: 0;
        }

        .game-rocket .rocket-wrap.boom .rocket-img {
            top: 0;
        }

        @media screen and (max-width: 621px) {
            .game-rocket .rocket-number {
                font-size: 4.0625rem;
            }
            .game-rocket .rocket-wrap {
                width: 5rem;
                margin-left: -2.5rem;
            }
            .game-rocket .rocket-wrap .rocket-fire {
                margin-top: -5.625rem;
                -webkit-transform: scale(0.8);
                transform: scale(0.8);
            }
        }

        .limbo-bg {
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
        }

        .cloud {
            position: absolute;
            width: 6.25rem;
            bottom: 7.5rem;
            left: 0.625rem;
        }

        .cloud-d {
            width: 4.375rem;
            left: 25%;
            bottom: 8.75rem;
        }

        .cloud-v {
            width: 6.25rem;
            left: 50%;
        }

        .cloud-g {
            width: 4.375rem;
            left: 80%;
            bottom: 8.75rem;
        }

        @include media-breakpoint-down(md) {
            .limbo-game-canvas {
                min-height: 21.875rem;
            }

            .cloud {
                width: 3.125rem;
                left: 1.25rem;
            }

            .cloud-d {
                width: 2.1875rem;
                left: 25%;
            }

            .cloud-v {
                width: 3.125rem;
                left: 65%;
            }

            .cloud-g {
                width: 2.1875rem;
                left: 85%;
            }
        }

        .game-content-limbo {
            display: flex;
            flex-direction: column;

            @include themed() {
                .limbo_canvas {
                    background-color: #1a191e;
                    min-height: 25rem;
                    overflow: hidden;
                    border-radius: 4px;
                    position: relative;
                    margin-left: -15px;
                    margin-top: -15px;
                    width: calc(100% + 30px);
                    margin-bottom: auto;
                }
            }

            .result_mul {
                display: flex;
                margin-top: auto;
                margin-bottom: auto;
                align-items: center;
                justify-content: center;
                font-size: 10em;
                transition: color 0.3s ease;
            }

            .limbo-footer {
                width: 100%;
                margin-bottom: 55px;
                margin-top: 35px;
            }

            .game-history {
                height: 53px;
            }

            input {
                box-shadow: 0 0 1px 1px transparent;
                transition: box-shadow 0.3s ease;
            }

            input.error {
                box-shadow: 0 0 1px 1px #db4437;
            }
        }

        @include media-breakpoint-down(sm) {
            .game-content-limbo {
                .result_mul {
                    font-size: 5em;
                    margin-bottom: 15px;
                }
            }
        }
    }
</style>
