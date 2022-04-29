import Seed from "../utils/Seed";

/**
 * Verifies a roulette game.
 * @class
 */
export default class Roulette {

    /**
     * Verifies a roulette game by returning the chosen pocket for a given seed_data object.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @return {integer} The chosen number
     */
    verify(seed_data) {
        return Math.floor(Seed.extractFloat(seed_data) * 37);
    }

}
