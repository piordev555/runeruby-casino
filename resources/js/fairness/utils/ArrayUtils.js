/**
 * Provides utility methods for creating and manipulating arrays.
 * @class
 */
export default class ArrayUtils {

    /**
     * Generates a new array containing a range of numbers from min to max.
     * @param {integer} min
     * @param {integer} max
     * @return {integer[]} The array covering the given range.
     */
    static generateArrayWithRange(min, max) {
        return Array.from({length: max - min + 1}, (_, index) => index + min);
    }

    /**
     * Generate a new array containing only unique items from the given array.
     * @param {Object[]} array
     * @return {Object[]} The array containing unique items.
     */
    static generateArrayOfUniqueItems(array) {
        return [...new Set(array)];
    }

    /**
     * Chunks array by given size.
     * @param {Object[]} array
     * @param {integer} size
     * @return {Object[]} The array split into chunks.
     */
    static chunkArray(array, size) {
        return Array.from({length: Math.ceil(array.length / size)}, (_, index) => array.slice(index * size, index * size + size));
    }

}
