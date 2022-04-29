import Seed from "../utils/Seed";

/**
 * Verifies a plinko game.
 * @class
 */
export default class Plinko {

    /**
     * Verifies a plinko game by returning the 16 chosen directions for a given seed_data object.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @return {integer} The directions.
     */
    verify(seed_data) {
        const directions = ['Left', 'Right'];
        return Seed.extractFloats(seed_data, 16).map((rowIndex) => directions[Math.floor(rowIndex * 2)]);
    }

}
