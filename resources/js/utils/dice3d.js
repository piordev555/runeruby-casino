import * as CANNON from 'cannon';
import * as THREE from 'three';
import { OrbitControls } from "three/examples/jsm/controls/OrbitControls";

const
    // Display boundaries
    debug = false,
    // Perform tests (performance-heavy) on throw or use precompiled map (var below)
    performTests = false;

const map = {"number_10":[1,1,1,1,1,1,1,1,1,1],"number_11":[1,1,1,1,2,1,1,1,1,1],"number_12":[1,1,2,1,1,2,1,1,1,1],"number_13":[2,2,2,1,1,1,1,1,1,1],"number_14":[1,2,2,2,2,1,1,1,1,1],"number_15":[2,2,2,2,2,1,1,1,1,1],"number_16":[2,1,2,2,2,2,1,1,2,1],"number_17":[1,1,2,1,2,1,3,1,1,4],"number_18":[2,2,1,2,2,1,1,1,2,4],"number_19":[2,2,1,1,1,3,2,1,3,3],"number_20":[2,1,2,2,2,2,1,2,3,3],"number_21":[3,1,1,2,2,1,2,3,2,4],"number_22":[2,2,3,1,3,2,2,2,1,4],"number_23":[3,1,3,1,3,1,2,3,3,3],"number_24":[3,2,2,3,1,1,3,2,1,6],"number_25":[3,3,1,1,1,2,4,4,3,3],"number_26":[2,1,2,2,1,4,1,1,6,6],"number_27":[3,3,2,1,2,2,3,1,4,6],"number_28":[1,5,3,4,4,1,2,1,1,6],"number_29":[2,3,3,1,3,1,4,3,4,5],"number_30":[1,4,1,3,1,4,3,2,5,6],"number_31":[2,4,4,2,2,4,4,3,1,5],"number_32":[1,4,3,3,3,2,3,5,2,6],"number_33":[4,3,3,1,3,4,1,3,5,6],"number_34":[3,4,3,4,1,1,2,4,6,6],"number_35":[4,4,2,2,4,3,1,6,3,6],"number_36":[1,4,5,3,5,4,3,1,4,6],"number_37":[2,4,3,4,6,4,4,3,1,6],"number_38":[5,4,1,2,3,5,6,3,3,6],"number_39":[4,3,3,3,6,5,3,4,2,6],"number_40":[4,3,4,4,4,5,3,1,6,6],"number_41":[5,6,3,5,6,1,3,3,3,6],"number_42":[5,6,6,5,5,2,2,3,2,6],"number_43":[5,4,2,4,5,5,3,4,5,6],"number_44":[5,2,2,6,4,4,6,6,3,6],"number_45":[6,2,3,6,4,4,5,3,6,6],"number_46":[6,2,6,4,4,4,6,4,4,6],"number_47":[5,3,6,4,6,3,2,6,6,6],"number_48":[4,6,3,6,3,5,5,6,4,6],"number_49":[6,3,4,6,6,4,6,5,3,6],"number_50":[6,4,5,6,6,2,3,6,6,6],"number_51":[2,5,6,4,5,6,6,6,5,6],"number_52":[5,3,4,6,6,5,6,5,6,6],"number_53":[5,6,6,5,6,5,6,6,2,6],"number_54":[6,6,6,6,6,6,2,4,6,6],"number_55":[5,6,4,6,6,4,6,6,6,6],"number_56":[6,6,6,6,6,6,5,3,6,6],"number_57":[6,6,6,6,3,6,6,6,6,6],"number_58":[6,6,6,4,6,6,6,6,6,6],"number_59":[6,6,6,6,6,5,6,6,6,6],"number_60":[6,6,6,6,6,6,6,6,6,6],"number_61":[3,3,9,4,7,13,8,3,8,3],"number_62":[3,3,11,3,4,13,6,6,10,3],"number_63":[3,5,3,4,3,20,3,6,11,5],"number_64":[4,10,9,6,4,6,10,3,7,5],"number_65":[5,7,5,5,7,13,9,5,6,3],"number_66":[6,6,8,4,7,12,5,4,8,6],"number_67":[4,9,17,4,4,4,8,6,7,4],"number_68":[5,9,18,4,9,7,3,3,5,5],"number_69":[3,7,10,6,3,17,3,4,11,5],"number_70":[5,7,12,6,5,10,8,6,8,3],"number_71":[4,8,13,6,9,10,4,4,8,5],"number_72":[5,8,15,6,8,12,5,3,6,4],"number_73":[3,5,16,4,7,10,7,6,9,6],"number_74":[5,4,16,5,4,17,4,3,12,4],"number_75":[6,11,8,6,10,12,4,4,11,3],"number_76":[4,5,15,4,12,7,7,6,11,5],"number_77":[5,6,17,3,3,18,8,5,7,5],"number_78":[3,6,17,4,3,17,8,6,11,3],"number_79":[5,9,3,6,6,20,10,4,11,5],"number_80":[6,10,17,6,7,5,8,3,12,6],"number_81":[4,8,14,3,11,14,8,5,11,3],"number_82":[5,10,19,3,12,12,10,4,3,4],"number_83":[3,5,9,5,10,17,12,5,11,6],"number_84":[3,7,19,4,6,20,9,4,7,5],"number_85":[6,8,15,6,7,17,8,4,11,3],"number_86":[4,8,18,4,8,17,10,3,9,5],"number_87":[3,5,19,6,10,9,12,6,11,6],"number_88":[6,12,11,5,10,18,7,4,9,6],"number_89":[6,8,15,4,12,8,12,6,12,6],"number_90":[6,5,18,5,12,16,11,4,8,5],"number_91":[6,11,19,6,8,11,11,6,7,6],"number_92":[6,6,15,6,10,19,11,6,7,6],"number_93":[6,6,11,6,10,20,12,6,10,6],"number_94":[6,11,18,6,6,19,7,6,9,6],"number_95":[6,9,20,6,9,17,8,6,8,6],"number_96":[6,12,19,6,8,15,8,6,10,6],"number_97":[6,10,20,6,7,19,10,6,7,6],"number_98":[6,12,16,6,11,15,8,6,12,6],"number_99":[6,10,11,6,11,19,12,6,12,6],"number_100":[6,9,18,6,11,16,12,6,10,6],"number_101":[6,8,18,6,11,20,10,6,10,6],"number_102":[6,12,19,6,12,19,8,6,8,6],"number_103":[6,10,18,6,12,20,8,6,11,6],"number_104":[6,8,20,6,8,20,12,6,12,6],"number_105":[6,12,20,6,9,19,12,6,9,6],"number_106":[6,12,16,6,11,20,11,6,12,6],"number_107":[6,12,20,6,12,19,8,6,12,6],"number_108":[6,12,19,6,12,18,11,6,12,6],"number_109":[6,11,20,6,12,19,12,6,11,6],"number_110":[6,12,19,6,12,20,12,6,11,6]};

class DiceManagerClass {
    constructor() {
        this.world = null;
    }

    setWorld(world) {
        this.world = world;

        this.diceBodyMaterial = new THREE.Material();
        this.floorBodyMaterial = new THREE.Material();
        this.barrierBodyMaterial = new THREE.Material();

        world.addContactMaterial(
            new CANNON.ContactMaterial(this.floorBodyMaterial, this.diceBodyMaterial, { friction: 0.01, restitution: 0.5 })
        );
        world.addContactMaterial(
            new CANNON.ContactMaterial(this.barrierBodyMaterial, this.diceBodyMaterial, { friction: 0, restitution: 1.0 })
        );
        world.addContactMaterial(
            new CANNON.ContactMaterial(this.diceBodyMaterial, this.diceBodyMaterial, { friction: 0, restitution: 0.5 })
        );
    }

    /**
     *
     * @param {array} diceValues
     * @param {DiceObject} [diceValues.dice]
     * @param {number} [diceValues.value]
     *
     */
    prepareValues(diceValues) {
        if (this.throwRunning) throw new Error('Cannot start another throw. Please wait, till the current throw is finished.');

        for (let i = 0; i < diceValues.length; i++)
            if (diceValues[i].value < 1 || diceValues[i].dice.values < diceValues[i].value)
                throw new Error('Cannot throw die to value ' + diceValues[i].value + ', because it has only ' + diceValues[i].dice.values + ' sides.');

        this.throwRunning = true;

        for (let i = 0; i < diceValues.length; i++) {
            diceValues[i].dice.simulationRunning = true;
            diceValues[i].vectors = diceValues[i].dice.getCurrentVectors();
            diceValues[i].stableCount = 0;
        }

        let check = () => {
            let allStable = true;
            for (let i = 0; i < diceValues.length; i++) {
                if (diceValues[i].dice.isFinished()) diceValues[i].stableCount++;
                else diceValues[i].stableCount = 0;

                if (diceValues[i].stableCount < 50) allStable = false;
            }

            if (allStable) {
                DiceManager.world.removeEventListener('postStep', check);

                for (let i = 0; i < diceValues.length; i++) {
                    diceValues[i].dice.shiftUpperValue(diceValues[i].value);
                    diceValues[i].dice.setVectors(diceValues[i].vectors);
                    diceValues[i].dice.simulationRunning = false;
                }

                this.throwRunning = false;
            } else DiceManager.world.step(DiceManager.world.dt);
        };

        this.world.addEventListener('postStep', check);
    }
}

class DiceObject {
    /**
     * @constructor
     * @param {object} options
     * @param {Number} [options.size = 100]
     * @param {Number} [options.fontColor = '#000000']
     * @param {Number} [options.backColor = '#ffffff']
     */
    constructor(options) {
        options = this.setDefaults(options, {
            size: 100,
            fontColor: '#000000',
            backColor: '#ffffff'
        });

        this.object = null;
        this.size = options.size;
        this.invertUpside = false;

        this.materialOptions = {
            specular: 0x1e292b,
            color: 0xf0f0f0,
            shininess: 2,
            flatShading: THREE.FlatShading,
        };
        this.labelColor = options.fontColor;
        this.diceColor = options.backColor;
    }

    setDefaults(options, defaults) {
        options = options || {};

        for (let key in defaults) {
            if (!defaults.hasOwnProperty(key)) continue;

            if (!(key in options)) {
                options[key] = defaults[key];
            }
        }

        return options;
    }

    emulateThrow(callback) {
        let stableCount = 0;

        let check = () => {
            if (this.isFinished()) {
                stableCount++;

                if (stableCount === 10) {
                    DiceManager.world.removeEventListener('postStep', check);
                    callback(this.getUpsideValue());
                }
            } else {
                stableCount = 0;
            }

            DiceManager.world.step(DiceManager.world.dt);
        };

        DiceManager.world.addEventListener('postStep', check);
    }

    isFinished() {
        let threshold = 1;

        let angularVelocity = this.object.body.angularVelocity;
        let velocity = this.object.body.velocity;

        return (Math.abs(angularVelocity.x) < threshold && Math.abs(angularVelocity.y) < threshold && Math.abs(angularVelocity.z) < threshold &&
            Math.abs(velocity.x) < threshold && Math.abs(velocity.y) < threshold && Math.abs(velocity.z) < threshold);
    }

    getUpsideValue() {
        let vector = new THREE.Vector3(0, this.invertUpside ? -1 : 1);
        let closest_face;
        let closest_angle = Math.PI * 2;
        for (let i = 0; i < this.object.geometry.faces.length; ++i) {
            let face = this.object.geometry.faces[i];
            if (face.materialIndex === 0) continue;

            let angle = face.normal.clone().applyQuaternion(this.object.body.quaternion).angleTo(vector);
            if (angle < closest_angle) {
                closest_angle = angle;
                closest_face = face;
            }
        }

        return closest_face.materialIndex - 1;
    }

    getCurrentVectors() {
        return {
            position: this.object.body.position.clone(),
            quaternion: this.object.body.quaternion.clone(),
            velocity: this.object.body.velocity.clone(),
            angularVelocity: this.object.body.angularVelocity.clone()
        };
    }

    setVectors(vectors) {
        this.object.body.position = vectors.position;
        this.object.body.quaternion = vectors.quaternion;
        this.object.body.velocity = vectors.velocity;
        this.object.body.angularVelocity = vectors.angularVelocity;
    }

    shiftUpperValue(toValue) {
        let geometry = this.object.geometry.clone();

        let fromValue = this.getUpsideValue();

        for (let i = 0, l = geometry.faces.length; i < l; ++i) {
            let materialIndex = geometry.faces[i].materialIndex;
            if (materialIndex === 0) continue;

            materialIndex += toValue - fromValue - 1;
            while (materialIndex > this.values) materialIndex -= this.values;
            while (materialIndex < 1) materialIndex += this.values;

            geometry.faces[i].materialIndex = materialIndex + 1;
        }

        this.updateMaterialsForValue(toValue - fromValue);

        this.object.geometry = geometry;
    }

    updateMaterialsForValue(diceValue) {}

    getChamferGeometry(vectors, faces, chamfer) {
        let chamfer_vectors = [], chamfer_faces = [], corner_faces = new Array(vectors.length);
        for (let i = 0; i < vectors.length; ++i) corner_faces[i] = [];
        for (let i = 0; i < faces.length; ++i) {
            let ii = faces[i], fl = ii.length - 1;
            let center_point = new THREE.Vector3();
            let face = new Array(fl);
            for (let j = 0; j < fl; ++j) {
                let vv = vectors[ii[j]].clone();
                center_point.add(vv);
                corner_faces[ii[j]].push(face[j] = chamfer_vectors.push(vv) - 1);
            }
            center_point.divideScalar(fl);
            for (let j = 0; j < fl; ++j) {
                let vv = chamfer_vectors[face[j]];
                vv.subVectors(vv, center_point).multiplyScalar(chamfer).addVectors(vv, center_point);
            }
            face.push(ii[fl]);
            chamfer_faces.push(face);
        }
        for (let i = 0; i < faces.length - 1; ++i) {
            for (let j = i + 1; j < faces.length; ++j) {
                let pairs = [], lastm = -1;
                for (let m = 0; m < faces[i].length - 1; ++m) {
                    let n = faces[j].indexOf(faces[i][m]);
                    if (n >= 0 && n < faces[j].length - 1) {
                        if (lastm >= 0 && m !== lastm + 1) pairs.unshift([i, m], [j, n]);
                        else pairs.push([i, m], [j, n]);
                        lastm = m;
                    }
                }
                if (pairs.length !== 4) continue;
                chamfer_faces.push([chamfer_faces[pairs[0][0]][pairs[0][1]],
                    chamfer_faces[pairs[1][0]][pairs[1][1]],
                    chamfer_faces[pairs[3][0]][pairs[3][1]],
                    chamfer_faces[pairs[2][0]][pairs[2][1]], -1]);
            }
        }
        for (let i = 0; i < corner_faces.length; ++i) {
            let cf = corner_faces[i], face = [cf[0]], count = cf.length - 1;
            while (count) {
                for (let m = faces.length; m < chamfer_faces.length; ++m) {
                    let index = chamfer_faces[m].indexOf(face[face.length - 1]);
                    if (index >= 0 && index < 4) {
                        if (--index === -1) index = 3;
                        let next_vertex = chamfer_faces[m][index];
                        if (cf.indexOf(next_vertex) >= 0) {
                            face.push(next_vertex);
                            break;
                        }
                    }
                }
                --count;
            }
            face.push(-1);
            chamfer_faces.push(face);
        }
        return { vectors: chamfer_vectors, faces: chamfer_faces };
    }

    makeGeometry(vertices, faces, radius, tab, af) {
        let geom = new THREE.Geometry();
        for (let i = 0; i < vertices.length; ++i) {
            let vertex = vertices[i].multiplyScalar(radius);
            vertex.index = geom.vertices.push(vertex) - 1;
        }
        for (let i = 0; i < faces.length; ++i) {
            let ii = faces[i], fl = ii.length - 1;
            let aa = Math.PI * 2 / fl;
            for (let j = 0; j < fl - 2; ++j) {
                geom.faces.push(new THREE.Face3(ii[0], ii[j + 1], ii[j + 2], [geom.vertices[ii[0]],
                    geom.vertices[ii[j + 1]], geom.vertices[ii[j + 2]]], 0, ii[fl] + 1));
                geom.faceVertexUvs[0].push([
                    new THREE.Vector2((Math.cos(af) + 1 + tab) / 2 / (1 + tab),
                        (Math.sin(af) + 1 + tab) / 2 / (1 + tab)),
                    new THREE.Vector2((Math.cos(aa * (j + 1) + af) + 1 + tab) / 2 / (1 + tab),
                        (Math.sin(aa * (j + 1) + af) + 1 + tab) / 2 / (1 + tab)),
                    new THREE.Vector2((Math.cos(aa * (j + 2) + af) + 1 + tab) / 2 / (1 + tab),
                        (Math.sin(aa * (j + 2) + af) + 1 + tab) / 2 / (1 + tab))]);
            }
        }
        geom.computeFaceNormals();
        geom.boundingSphere = new THREE.Sphere(new THREE.Vector3(), radius);
        return geom;
    }

    createShape(vertices, faces, radius) {
        let cv = new Array(vertices.length), cf = new Array(faces.length);
        for (let i = 0; i < vertices.length; ++i) {
            let v = vertices[i];
            cv[i] = new CANNON.Vec3(v.x * radius, v.y * radius, v.z * radius);
        }
        for (let i = 0; i < faces.length; ++i) {
            cf[i] = faces[i].slice(0, faces[i].length - 1);
        }
        return new CANNON.ConvexPolyhedron(cv, cf);
    }

    getGeometry() {
        let radius = this.size * this.scaleFactor;

        let vectors = new Array(this.vertices.length);
        for (let i = 0; i < this.vertices.length; ++i) {
            vectors[i] = (new THREE.Vector3).fromArray(this.vertices[i]).normalize();
        }

        let chamferGeometry = this.getChamferGeometry(vectors, this.faces, this.chamfer);
        let geometry = this.makeGeometry(chamferGeometry.vectors, chamferGeometry.faces, radius, this.tab, this.af);
        geometry.cannon_shape = this.createShape(vectors, this.faces, radius);

        return geometry;
    }

    calculateTextureSize(approx) {
        return Math.max(128, Math.pow(2, Math.floor(Math.log(approx) / Math.log(2))));
    }

    createTextTexture(text, color, backColor) {
        let canvas = document.createElement("canvas");
        let context = canvas.getContext("2d");
        let ts = this.calculateTextureSize(this.size / 2 + this.size * this.textMargin) * 2;
        canvas.width = canvas.height = ts;
        context.font = ts / (1 + 2 * this.textMargin) + "pt Arial";
        context.fillStyle = backColor;
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.textAlign = "center";
        context.textBaseline = "middle";
        context.fillStyle = color;
        context.fillText(text, canvas.width / 2, canvas.height / 2);
        let texture = new THREE.Texture(canvas);
        texture.needsUpdate = true;
        return texture;
    }

    getMaterials() {
        let materials = [];
        for (let i = 0; i < this.faceTexts.length; ++i) {
            let texture = null;
            if (this.customTextTextureFunction) {
                texture = this.customTextTextureFunction(this.faceTexts[i], this.labelColor, this.diceColor);
            } else {
                texture = this.createTextTexture(this.faceTexts[i], this.labelColor, this.diceColor);
            }

            materials.push(new THREE.MeshPhongMaterial(Object.assign({}, this.materialOptions, { map: texture })));
        }
        return materials;
    }

    getObject() {
        return this.object;
    }

    create() {
        if (!DiceManager.world) throw new Error('You must call DiceManager.setWorld(world) first.');
        this.object = new THREE.Mesh(this.getGeometry(), this.getMaterials());

        this.object.reveiceShadow = true;
        this.object.castShadow = true;
        this.object.diceObject = this;
        this.object.body = new CANNON.Body({
            mass: this.mass,
            shape: this.object.geometry.cannon_shape,
            material: DiceManager.diceBodyMaterial
        });
        this.object.body.linearDamping = 0.1;
        this.object.body.angularDamping = 0.1;
        DiceManager.world.add(this.object.body);

        return this.object;
    }

    updateMeshFromBody() {
        if (!this.simulationRunning) {
            this.object.position.copy(this.object.body.position);
            this.object.quaternion.copy(this.object.body.quaternion);
        }
    }

    updateBodyFromMesh() {
        this.object.body.position.copy(this.object.position);
        this.object.body.quaternion.copy(this.object.quaternion);
    }
}

class DiceD6 extends DiceObject {
    constructor(options) {
        super(options);

        this.tab = 0.1;
        this.af = Math.PI / 4;
        this.chamfer = 0.96;
        this.vertices = [[-1, -1, -1], [1, -1, -1], [1, 1, -1], [-1, 1, -1],
            [-1, -1, 1], [1, -1, 1], [1, 1, 1], [-1, 1, 1]];
        this.faces = [[0, 3, 2, 1, 1], [1, 2, 6, 5, 2], [0, 1, 5, 4, 3],
            [3, 7, 6, 2, 4], [0, 4, 7, 3, 5], [4, 5, 6, 7, 6]];
        this.scaleFactor = 0.7;
        this.values = 6;
        this.faceTexts = [' ', '0', '1', '2', '3', '4', '5', '6', '7', '8',
            '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20'];
        this.textMargin = 1.0;
        this.mass = 300;
        this.inertia = 13;

        this.create();
    }
}

export class DiceD12 extends DiceObject {
    constructor(options) {
        super(options);

        let p = (1 + Math.sqrt(5)) / 2;
        let q = 1 / p;

        this.tab = 0.2;
        this.af = -Math.PI / 4 / 2;
        this.chamfer = 0.968;
        this.vertices = [[0, q, p], [0, q, -p], [0, -q, p], [0, -q, -p], [p, 0, q],
            [p, 0, -q], [-p, 0, q], [-p, 0, -q], [q, p, 0], [q, -p, 0], [-q, p, 0],
            [-q, -p, 0], [1, 1, 1], [1, 1, -1], [1, -1, 1], [1, -1, -1], [-1, 1, 1],
            [-1, 1, -1], [-1, -1, 1], [-1, -1, -1]];
        this.faces = [[2, 14, 4, 12, 0, 1], [15, 9, 11, 19, 3, 2], [16, 10, 17, 7, 6, 3], [6, 7, 19, 11, 18, 4],
            [6, 18, 2, 0, 16, 5], [18, 11, 9, 14, 2, 6], [1, 17, 10, 8, 13, 7], [1, 13, 5, 15, 3, 8],
            [13, 8, 12, 4, 5, 9], [5, 4, 14, 9, 15, 10], [0, 12, 8, 10, 16, 11], [3, 19, 7, 17, 1, 12]];
        this.scaleFactor = 0.7;
        this.values = 12;
        this.faceTexts = [' ', '0', '1', '2', '3', '4', '5', '6', '7', '8',
            '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20'];
        this.textMargin = 1.0;
        this.mass = 350;
        this.inertia = 8;

        this.create();
    }
}

export class DiceD20 extends DiceObject {
    constructor(options) {
        super(options);

        let t = (1 + Math.sqrt(5)) / 2;

        this.tab = -0.2;
        this.af = -Math.PI / 4 / 2;
        this.chamfer = 0.955;
        this.vertices = [[-1, t, 0], [1, t, 0], [-1, -t, 0], [1, -t, 0],
            [0, -1, t], [0, 1, t], [0, -1, -t], [0, 1, -t],
            [t, 0, -1], [t, 0, 1], [-t, 0, -1], [-t, 0, 1]];
        this.faces = [[0, 11, 5, 1], [0, 5, 1, 2], [0, 1, 7, 3], [0, 7, 10, 4], [0, 10, 11, 5],
            [1, 5, 9, 6], [5, 11, 4, 7], [11, 10, 2, 8], [10, 7, 6, 9], [7, 1, 8, 10],
            [3, 9, 4, 11], [3, 4, 2, 12], [3, 2, 6, 13], [3, 6, 8, 14], [3, 8, 9, 15],
            [4, 9, 5, 16], [2, 4, 11, 17], [6, 2, 10, 18], [8, 6, 7, 19], [9, 8, 1, 20]];
        this.scaleFactor = 0.8;
        this.values = 20;
        this.faceTexts = [' ', '0', '1', '2', '3', '4', '5', '6', '7', '8',
            '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20'];
        this.textMargin = 1.0;
        this.mass = 400;
        this.inertia = 6;

        this.create();
    }
}

const DiceManager = new DiceManagerClass();

export default class Dice {

    constructor(vm, container) {
        this.scene = new THREE.Scene();
        this.vm = vm;
        const SCREEN_WIDTH = 800, SCREEN_HEIGHT = 320 /* 600 */;
        const VIEW_ANGLE = 15, ASPECT = SCREEN_WIDTH / SCREEN_HEIGHT, NEAR = 0.01, FAR = 20000;

        this.camera = new THREE.PerspectiveCamera(VIEW_ANGLE, ASPECT, NEAR, FAR);
        this.scene.add(this.camera);

        this.camera.position.set(2.9705537233531973e-7, 99.11186719495485, -0.00009911142203087737);
        this.camera.rotation.set(-1.5707973267904052, 2.9971725962630452e-9, 3.138595476506083);
        this.camera.quaternion.set(0.0010596622533596265, -0.001059661193697903, 0.7071063407408456, 0.7071056336348585);

        this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        this.renderer.setClearColor(0x000000, 0);
        this.renderer.setSize(SCREEN_WIDTH, SCREEN_HEIGHT);
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;

        container.appendChild(this.renderer.domElement);

        this.controls = new OrbitControls(this.camera, this.renderer.domElement);
        if(!debug) this.controls.enabled = false;

        let ambient = new THREE.AmbientLight('#ffffff', 0.7);
        this.scene.add(ambient);

        let directionalLight = new THREE.DirectionalLight('#ffffff', 0.7);
        directionalLight.position.x = -1000;
        directionalLight.position.y = 1000;
        directionalLight.position.z = 1000;
        this.scene.add(directionalLight);

        this.scene.fog = new THREE.FogExp2(0x9999ff, 0.00025);

        this.world = new CANNON.World();

        this.world.gravity.set(0, -9.82 * 20, 0);
        this.world.broadphase = new CANNON.NaiveBroadphase();
        this.world.solver.iterations = 16;

        DiceManager.setWorld(this.world);

        let floorBody = new CANNON.Body({mass: 0, shape: new CANNON.Plane(), material: DiceManager.floorBodyMaterial});
        floorBody.quaternion.setFromAxisAngle(new CANNON.Vec3(1, 0, 0), -Math.PI / 2);
        this.world.add(floorBody);

        floorBody.addEventListener('collide', () => vm.playSound(`/sounds/roll1.wav`, 50));

        const walls = {
            left: 16,
            right: -16,
            bottom: 12,
            top: -11
        }

        let barrier = new CANNON.Body({mass: 0, shape: new CANNON.Plane(), material: DiceManager.barrier_body_material});
        barrier.quaternion.setFromAxisAngle(new CANNON.Vec3(0, 1, 0), Math.PI);
        barrier.position.set(0, 0, walls.bottom);
        this.world.add(barrier);

        barrier = new CANNON.Body({mass: 0, shape: new CANNON.Plane(), material: DiceManager.barrier_body_material});
        barrier.quaternion.setFromAxisAngle(new CANNON.Vec3(0, 0, 1), -Math.PI);
        barrier.position.set(walls.top, walls.top, walls.top);
        this.world.add(barrier);

        barrier = new CANNON.Body({mass: 0, shape: new CANNON.Plane(), material: DiceManager.barrier_body_material});
        barrier.quaternion.setFromAxisAngle(new CANNON.Vec3(0, 1, 0), -Math.PI / 2);
        barrier.position.set(walls.left, 0, 0);
        this.world.add(barrier);

        barrier = new CANNON.Body({mass: 0, shape: new CANNON.Plane(), material: DiceManager.barrier_body_material});
        barrier.quaternion.setFromAxisAngle(new CANNON.Vec3(0, 1, 0), Math.PI / 2);
        barrier.position.set(walls.right, 0, 0);
        this.world.add(barrier);

        if(debug) {
            $('canvas').css({border: '1px solid red'});

            let floorMaterial = new THREE.MeshPhongMaterial({color: '#00aa00', side: THREE.DoubleSide});
            let floorGeometry = new THREE.PlaneGeometry(30, 30, 10, 10);
            let floor = new THREE.Mesh(floorGeometry, floorMaterial);
            floor.receiveShadow = true;
            floor.rotation.x = Math.PI / 2;
            this.scene.add(floor);

            floorMaterial = new THREE.MeshPhongMaterial({color: '#ffffff', side: THREE.DoubleSide});
            floor = new THREE.Mesh(floorGeometry, floorMaterial);
            floor.receiveShadow = true;
            floor.rotation.x = Math.PI / 2;
            floor.rotation.y = Math.PI / 2;
            floor.position.set(walls.left, 0, 0);
            this.scene.add(floor);

            floorMaterial = new THREE.MeshPhongMaterial({color: '#ffffff', side: THREE.DoubleSide});
            floor = new THREE.Mesh(floorGeometry, floorMaterial);
            floor.receiveShadow = true;
            floor.rotation.x = Math.PI / 2;
            floor.rotation.y = Math.PI / 2;
            floor.position.set(walls.right, 0, 0);
            this.scene.add(floor);

            floorMaterial = new THREE.MeshPhongMaterial({color: '#ffffff', side: THREE.DoubleSide});
            floor = new THREE.Mesh(floorGeometry, floorMaterial);
            floor.receiveShadow = true;
            floor.rotation.z = Math.PI / 2;
            floor.position.set(0, 0, walls.bottom);
            this.scene.add(floor);

            floorMaterial = new THREE.MeshPhongMaterial({color: '#ffffff', side: THREE.DoubleSide});
            floor = new THREE.Mesh(floorGeometry, floorMaterial);
            floor.receiveShadow = true;
            floor.rotation.z = Math.PI / 2;
            floor.position.set(0, 0, walls.top);
            this.scene.add(floor);
        }
    }

    throw(value) {
        if(this.pushedDice === undefined) {
            let dice = [];

            let color = 'white';
            let instances = [
                new DiceD6({ size: 1.5, backColor: color }),
                new DiceD12({ size: 1.5, backColor: color }),
                new DiceD20({ size: 1.5, backColor: color }),
                new DiceD6({ size: 1.5, backColor: color }),
                new DiceD12({ size: 1.5, backColor: color }),
                new DiceD20({ size: 1.5, backColor: color }),
                new DiceD12({ size: 1.5, backColor: color }),
                new DiceD6({ size: 1.5, backColor: color }),
                new DiceD12({ size: 1.5, backColor: color }),
                new DiceD6({ size: 1.5, backColor: color })
            ];

            for (let i = 0; i < instances.length; i++) {
                this.scene.add(instances[i].getObject());
                dice.push(instances[i]);
            }

            this.dice = dice;

            requestAnimationFrame(() => this.animate(this));

            this.pushedDice = true;
        }

        if(performTests) {
            const length = [6, 12, 20, 6, 12, 20, 12, 6, 12, 6];

            const performTest = () => {
                return new Promise((resolve) => {
                    if (value > 6 + 12 + 20 + 6 + 12 + 20 + 12 + 6 + 12 + 6) {
                        console.error('Impossible to throw ' + value);
                        resolve([1, 1, 1, 1, 1, 1, 1, 1, 1, 1]);
                        return;
                    }

                    let values = [], now = +new Date();

                    // Perform performance-heavy test, since each number will require individual max value
                    if (value > 6 * length.length) {
                        while (true) {
                            let v = [], currentSum = 0, i = 0;

                            while (true) {
                                if (length[i] === undefined) break;
                                let int = this.vm.random(value > 90 ? 6 : 3, length[i]);

                                if (int > length[i] || int < 1) continue;
                                if (currentSum + int > value || v.length > length.length) {
                                    if (debug) console.log('INVALID', `N ${value}`, `R ${currentSum}`, v, int);
                                    break;
                                }

                                i++;

                                currentSum += int;
                                v.push(int);

                                if (currentSum === value && v.length === length.length) {
                                    values = v;
                                    if (debug) console.log(currentSum, value)
                                    break;
                                }
                            }

                            if (values.length > 0) break;
                        }
                    }
                    // Perform simple test: all dice types support values up to 6, so number could be split easily
                    else {
                        const getRandomCollection = (min, max, length, sum) => {
                            let collection = [], leftSum = sum - (min - 1);

                            for (let i = 0; i < length - 1; i++) {
                                let number = this.vm.random(min, Math.min(Math.ceil(leftSum / (length - i)), max));
                                leftSum -= number;
                                collection.push(number);
                            }
                            leftSum += min - 1;
                            while (leftSum > max) {
                                let randomIndex = Math.floor(Math.random() * collection.length);
                                if (collection[randomIndex] < max) {
                                    collection[randomIndex]++;
                                    leftSum--;
                                }
                            }

                            collection.push(leftSum);
                            return collection;
                        }

                        values = getRandomCollection(1, 6, length.length, value);
                    }

                    resolve(values);
                    if (debug) console.log(`Test took ${+new Date() - now}ms for value ${value} (${value > 6 * length.length ? 'Performance' : 'Simple'} test)`);
                });
            }

            performTest().then((values) => {
                if (debug) console.log(values);
                this.randomDiceThrow(this.dice, values);
            });
        } else this.randomDiceThrow(this.dice, map['number_'+value]);
    }

    randomDiceThrow(dice, values) {
        let diceValues = [];

        for(let i = 0; i < dice.length; i++) {
            let yRand = Math.random() * 20;
            dice[i].getObject().position.x = Math.random() * (i + 5.1);
            dice[i].getObject().position.y = Math.random() * (i + 5.1);
            dice[i].getObject().position.z = Math.random() * (i + 5.1);
            dice[i].getObject().quaternion.x = (Math.random() * 90-45) * Math.PI / 180;
            dice[i].getObject().quaternion.z = (Math.random() * 90-45) * Math.PI / 180;
            dice[i].updateBodyFromMesh();
            let rand = Math.random() * 5;
            dice[i].getObject().body.velocity.set(25 + rand, 40 + yRand, 15 + rand);
            dice[i].getObject().body.angularVelocity.set(20 * Math.random() -10, 20 * Math.random() -10, 20 * Math.random() -10);

            //if(debug) console.log(i, dice[i], i, values[i]);
            diceValues.push({ dice: dice[i], value: values[i] });
        }

        DiceManager.prepareValues(diceValues);
    }

    updatePhysics() {
        this.world.step(1.0 / 60.0);
        for(let i in this.dice) this.dice[i].updateMeshFromBody();
    }

    update() {
        this.controls.update();
    }

    render() {
        this.renderer.render(this.scene, this.camera);
    }

    animate(instance) {
        instance.updatePhysics(instance.dice);
        instance.render();
        instance.update();

        requestAnimationFrame(() => this.animate(instance));
    }

}
