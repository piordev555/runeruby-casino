import ArrayUtils from "../utils/ArrayUtils";
import Seed from "../utils/Seed";

/**
 * Verifies a stairs game.
 * @class
 */
export default class Tower {

    /**
     * Verifies a tower game by returning the X chosen mines for each row for a given seed_data object.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @param {integer} mines
     * @return {object}
     */
    verify(seed_data, mines) {
        const columns = 4, rows = 10;

        let output = [];
        for(let i = 1; i <= rows; i++) {
            let array = ArrayUtils.generateArrayWithRange(0, columns);
            output.push(Seed.extractFloats(seed_data, columns * i).splice(i - 1, i * mines).map((float, index) => array.splice(Math.floor(float * (columns - (index * 2) + 1)), 1)[0]).splice(0, mines));
        }

        return output;
    }

}
