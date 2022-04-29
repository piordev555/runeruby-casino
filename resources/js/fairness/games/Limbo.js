import Seed from "../utils/Seed";

/**
 * Verifies a limbo game.
 * @class
 */
export default class Limbo {

    /**
     * Verifies a limbo game by returning the stoppage point for a given seed_data object.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @param {integer} seed_data.houseEdge
     * @return {float} the float
     */
    verify(seed_data) {
        const max_multiplier = 1e8, house_edge = seed_data.houseEdge;
        const float_point = max_multiplier / (Seed.extractFloat(seed_data) * max_multiplier) * house_edge;
        return (Math.floor(float_point * 100) / 100).toFixed(2);
    }

}
