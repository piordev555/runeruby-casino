import ArrayUtils from "../utils/ArrayUtils";
import Seed from "../utils/Seed";

/**
 * Verifies a keno game.
 * @class
 */
export default class Keno {

    /**
     * Verifies a keno game by returning the 10 chosen tiles for a given seed_data object.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @return {integer} The tiles
     */
    verify(seed_data) {
        const max_squares = 39;
        const squares = ArrayUtils.generateArrayWithRange(0, max_squares);
        return Seed.extractFloats(seed_data, 10).map((float, index) => squares.splice(Math.floor(float * (max_squares - index + 1)), 1)[0]);
    }

}
