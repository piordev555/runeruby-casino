import ArrayUtils from "./ArrayUtils";
import {sha256} from 'js-sha256';

/**
 * Provides utility method for extracting floats from a game seed.
 * @class
 */
export default class Seed {

    /**
     * Given a seed_data object, will extract a single float from its HMAC_SHA256 sequence.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.client_seed
     * @return {float}
     */
    static extractFloat(seed_data) {
        return Seed.extractFloats(seed_data, 1)[0];
    }

    /**
     * Given a seed_data object, will extract a given number of floats from its HMAC_SHA256 sequence.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.client_seed
     * @param {integer} seed_data.nonce
     * @param {integer} count The number of floats to extract
     * @return {float[]}
     */
    static extractFloats(seed_data, count) {
        const byteGenerator = Seed._byteGenerator(seed_data);
        const bytes = Array.from({length: count * 4}, () => byteGenerator.next().value);
        const byte_to_float = (byte) => byte.reduce((result, value, index) => {
            return result + (value / (256 ** (index + 1)))
        }, 0);
        return ArrayUtils.chunkArray(bytes, 4).map(byte_to_float);
    }

    /**
     * Given a seed_data object will keep yielding the next byte from its HMAC_SHA256 sequence.
     * Please be aware this method will start with cursor set to zero,
     * and in the case of all 32 bytes getting used it will increment the cursor to generate a new HMAC_SHA256 sequence.
     * @param {Object} seed_data
     * @param {string} seed_data.serverSeed
     * @param {string} seed_data.clientSeed
     * @param {integer} seed_data.nonce
     * @yields {number} the next byte in the HMAC_SHA256 sequence
     */
    static* _byteGenerator({serverSeed, clientSeed, nonce}) {
        let currentRound = 0;
        while(true) {
            const hash = sha256.hmac.create(serverSeed);
            hash.update(`${clientSeed}:${nonce}:${currentRound++}`);

            const buffer = hash.digest();
            for(let i = 0; i < 32; i++) yield Number(buffer[i]);
        }
    }

}
