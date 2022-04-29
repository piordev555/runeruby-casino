import Seed from "../utils/Seed";

/**
 * Verifies a dice game.
 * @class
 */
export default class Dice {

    /**
     * Verifies a dice roll by returning the roll number for a given seed_data object.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @return {float} the float
     */
    verify(seed_data) {
        return Math.floor(Seed.extractFloat(seed_data) * 101) + 10;
    }

}
