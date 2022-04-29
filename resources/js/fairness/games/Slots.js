import Seed from "../utils/Seed";

/**
 * Verifies a slots game.
 * @class
 */
export default class Slots {

    /**
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @return {integer} The chosen number
     */
    verify(seed_data) {
        const result = [],
            icons = ['apple', 'bananas', 'cherry', 'grapes', 'orange', 'pineapple', 'strawberry', 'watermelon', 'lemon', 'kiwi', 'raspberry', 'wild', 'scatter'],
            floats = Seed.extractFloats(seed_data, 5 * 3);
        let total = 0;
        for(let i = 0; i < 5; i++) {
            const column = [];
            for (let j = 0; j < 3; j++) {
                column.push(icons[Math.floor(floats[total] * icons.length)]);
                total++;
            }
            result.push(column);
        }
        return result;
    }

}
