import Seed from "../utils/Seed";

/**
 * Verifies a coinflip game.
 * @class
 */
export default class Coinflip {

    /**
     * Verifies a coinflip game by returning the 52 chosen coin sides for a given seed_data object.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @return {integer} The sides.
     */
    verify(seed_data) {
        const directions = ['blue', 'yellow'];
        return Seed.extractFloats(seed_data, 52).map((rowIndex) => directions[Math.floor(rowIndex * 2)]);
    }

}
