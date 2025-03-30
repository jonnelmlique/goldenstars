import './bootstrap';
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';

// Make THREE and OrbitControls globally available
window.THREE = THREE;
window.OrbitControls = OrbitControls;

// Initialize any needed THREE.js functionality
THREE.ColorManagement.enabled = true;
