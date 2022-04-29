import ArrayUtils from "../utils/ArrayUtils";
import Seed from "../utils/Seed";

/**
 * Verifies a stairs game.
 * @class
 */
export default class Stairs {

    /**
     * Verifies a stairs game by returning the X chosen mines for each row for a given seed_data object.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @param {integer} mines
     * @return {object}
     */
    verify(seed_data, mines) {
        const rows = [
            20,
            19,
            19,
            18,
            19,
            15,
            17,
            13,
            12,
            19,
            10,
            9,
            8
        ];

        let output = [];
        for(let i = 1; i <= rows.length; i++) {
            let array = ArrayUtils.generateArrayWithRange(0, rows[i - 1]);
            output.push(Seed.extractFloats(seed_data, rows[i - 1] * i).splice(i - 1, mines * i).map((float, index) => array.splice(Math.floor(float * (rows[i - 1] - index + 1)), 1)[0]).splice(0, mines));
        }

        return output;
    }

}
