import Seed from "../utils/Seed";

/**
 * Verifies a wheel game.
 * @class
 */
export default class WheelX50 {

    /**
     * Verifies a wheel game by returning the chosen result for given seed_data object and segments.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @return {string[]} The directions.
     */
    verify(seed_data) {
        return Math.floor(Seed.extractFloat(seed_data) * 56);
    }

}
