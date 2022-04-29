import ArrayUtils from "../utils/ArrayUtils";
import Seed from "../utils/Seed";

/**
 * Verifies all card games.
 * @class
 */
export default class Cards {

    /**
     * Verifies a blackjack card game by returning the first 52 cards for a given seed_data object.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @return {Object[]} The cards.
     */
    verifyBlackjack(seed_data) {
        return this._getCards(seed_data, 52);
    }

    /**
     * Verifies a hilo card game by returning the first 52 cards for a given seed_data object.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @return {Object[]} The cards.
     */
    verifyHilo(seed_data) {
        return this._getCards(seed_data, 1);
    }

    /**
     * Verifies a video poker card game by returning the first 10 cards for a given seed_data object.
     * Uses the fisher-yates shuffle.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @return {Object[]} The cards.
     */
    verifyVideoPoker(seed_data) {
        return this._getCards(seed_data, 10, true);
    }

    /**
     * Verifies a baccarat card game by returning the first 6 cards for a given seed_data object.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @return {Object[]} The cards.
     */
    verifyBaccarat(seed_data) {
        return this._getCards(seed_data, 6);
    }

    /**
     * Verifies a diamond poker card game by returning the first 10 cards for a given seed_data object.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @return {Object[]} The cards.
     */
    verifyDiamondPoker(seed_data) {
        const gems = ['green', 'purple', 'yellow', 'red', 'light_blue', 'pink', 'blue'];
        const max_gems = 5;
        return Seed.extractFloats(seed_data, max_gems).map((cardIndex) => gems[Math.floor(cardIndex * 7)]);
    }

    /**
     * Given a seed_data object, will get the given number of cards from the HMAC_SHA256 sequence it generates - supports fisher yates.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @param {integer} count The number of cards.
     * @param {boolean} [fisher_yates=false] Use fisher yates.
     * @return {object[]} The cards.
     */
    _getCards(seed_data, count, fisher_yates = false) {
        const cards = ArrayUtils.generateArrayWithRange(0, 207);
        return Seed.extractFloats(seed_data, count).map((cardIndex, index) =>
            fisher_yates ? cards.splice(Math.floor(cardIndex * (52 - index)), 1)[0]
            : cards[Math.floor(cardIndex * 52)]);
    }

}
