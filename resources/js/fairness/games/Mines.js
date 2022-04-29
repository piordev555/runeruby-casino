import ArrayUtils from "../utils/ArrayUtils";
import Seed from "../utils/Seed";

/**
 * Verifies a mines game.
 * @class
 */
export default class Mines {

    /**
     * Verifies a mines game by returning the 24 chosen mines for a given seed_data object.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @return {integer} The chosen number
     */
    verify(seed_data) {
        const max_mines = 24;
        const mines = ArrayUtils.generateArrayWithRange(0, max_mines);
        return Seed.extractFloats(seed_data, max_mines).map((float, index) => mines.splice(Math.floor(float * (max_mines - index + 1)), 1)[0]);
    }

}
