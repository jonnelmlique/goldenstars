<x-filament::page>
    <div class="space-y-6">
        <div class="flex justify-center">
            <div id="warehouse-container" class="w-full max-w-5xl h-[600px] bg-gray-100 dark:bg-gray-900 rounded-lg">
                <canvas id="three-canvas" class="w-full h-full"></canvas>
            </div>
        </div>

        {{-- Location List --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($locations as $location)
                <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
                    <h3 class="text-lg font-medium">{{ $location->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $location->code }}</p>
                    <div class="mt-2">
                        <span class="text-sm font-medium">Shelves: {{ $location->shelves->count() }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Modal --}}
        <div id="shelf-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div
                        class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:p-6">
                        {{-- Header with close button --}}
                        <div
                            class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-3 mb-4">
                            <div class="w-8">{{-- Spacer --}}</div>
                            <h3 id="modal-title"
                                class="text-xl font-semibold leading-6 text-gray-900 dark:text-gray-100 flex-1 text-center">
                            </h3>
                            <button type="button" onclick="window.closeShelfModal()"
                                class="rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 p-2 text-gray-400 hover:text-gray-500 focus:outline-none">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div id="modal-content" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/app.js'])
        <script>
            // Expose modal functions globally
            window.handleEscapeKey = function (event) {
                if (event.key === 'Escape') {
                    window.closeShelfModal();
                }
            };

            window.handleOutsideClick = function (event) {
                const modal = document.getElementById('shelf-modal');
                const modalContent = modal.querySelector('.rounded-lg');
                if (event.target === modal || !modalContent.contains(event.target)) {
                    window.closeShelfModal();
                }
            };

            window.closeShelfModal = function () {
                const modal = document.getElementById('shelf-modal');
                if (modal) {
                    modal.classList.add('hidden');
                    document.removeEventListener('keydown', window.handleEscapeKey);
                    document.removeEventListener('click', window.handleOutsideClick);
                }
            };

            document.addEventListener('DOMContentLoaded', function () {
                const canvas = document.getElementById('three-canvas');
                const scene = new window.THREE.Scene();
                const camera = new window.THREE.PerspectiveCamera(75, canvas.clientWidth / canvas.clientHeight, 0.1, 1000);
                const renderer = new window.THREE.WebGLRenderer({
                    canvas: canvas,
                    antialias: true
                });

                renderer.setSize(canvas.clientWidth, canvas.clientHeight);
                renderer.setClearColor(0xf0f0f0);

                // Add lighting
                const ambientLight = new window.THREE.AmbientLight(0xffffff, 0.6);
                scene.add(ambientLight);

                const directionalLight = new window.THREE.DirectionalLight(0xffffff, 0.8);
                directionalLight.position.set(10, 10, 10);
                scene.add(directionalLight);

                // Initialize locations first
                const locations = @json($locations);

                // Add available locations grid
                function createAvailableLocationsGrid(scene) {
                    const gridSize = 50;
                    const cellSize = 7;
                    const spacing = 8;

                    for (let x = -gridSize / 2; x <= gridSize / 2; x += spacing) {
                        for (let z = -gridSize / 2; z <= gridSize / 2; z += spacing) {
                            const locationExists = locations.some(loc =>
                                Math.abs(loc.x_position - x) < 0.1 &&
                                Math.abs(loc.z_position - z) < 0.1
                            );

                            if (!locationExists) {
                                // Create floor with grid pattern
                                const floorGeometry = new window.THREE.PlaneGeometry(cellSize, cellSize);
                                const floorMaterial = new window.THREE.MeshStandardMaterial({
                                    color: 0x90EE90,
                                    transparent: true,
                                    opacity: 0.3,
                                    metalness: 0.2,
                                    roughness: 0.8
                                });
                                const floor = new window.THREE.Mesh(floorGeometry, floorMaterial);
                                floor.rotation.x = -Math.PI / 2;
                                floor.position.set(x, -0.1, z);

                                // Add grid lines
                                const gridHelper = new window.THREE.GridHelper(cellSize, 8, 0x006400, 0x008000);
                                gridHelper.position.set(x, 0, z);

                                // Create floating coordinate display
                                const canvas = document.createElement('canvas');
                                const context = canvas.getContext('2d');
                                canvas.width = 512; // Doubled width
                                canvas.height = 128; // Doubled height

                                // Background with gradient
                                const gradient = context.createLinearGradient(0, 0, 0, canvas.height);
                                gradient.addColorStop(0, 'rgba(0, 100, 0, 0.9)');
                                gradient.addColorStop(1, 'rgba(0, 100, 0, 0.7)');
                                context.fillStyle = gradient;
                                context.fillRect(0, 0, canvas.width, canvas.height);

                                // Add border
                                context.strokeStyle = '#ffffff';
                                context.lineWidth = 4; // Thicker border
                                context.strokeRect(0, 0, canvas.width, canvas.height);

                                // Simple coordinate text with larger font
                                context.font = 'bold 64px Arial'; // Doubled font size
                                context.fillStyle = '#ffffff';
                                context.textAlign = 'center';
                                context.textBaseline = 'middle';
                                context.fillText(`X ${x}, Y 0, Z ${z}`, canvas.width / 2, canvas.height / 2);

                                const texture = new window.THREE.CanvasTexture(canvas);
                                const labelGeometry = new window.THREE.PlaneGeometry(4, 1); // Doubled geometry size
                                const labelMaterial = new window.THREE.MeshBasicMaterial({
                                    map: texture,
                                    transparent: true,
                                    side: window.THREE.DoubleSide,
                                    depthWrite: false
                                });
                                const label = new window.THREE.Mesh(labelGeometry, labelMaterial);

                                // Make label float and always face camera
                                label.position.set(x, 2, z); // Float 2 units above the floor
                                label.rotation.x = -Math.PI / 4; // Tilt for better visibility

                                // Add a simple animation to make it float
                                const animate = () => {
                                    label.position.y = 2 + Math.sin(Date.now() * 0.002) * 0.1; // Gentle floating motion
                                    requestAnimationFrame(animate);
                                };
                                animate();

                                scene.add(floor);
                                scene.add(gridHelper);
                                scene.add(label);

                                // Add label to an array for updating rotation
                                if (!window.coordinateLabels) {
                                    window.coordinateLabels = [];
                                }
                                window.coordinateLabels.push(label);
                            }
                        }
                    }
                }

                // Create warehouse locations
                createAvailableLocationsGrid(scene);
                locations.forEach(location => {
                    createLocation(scene, location);
                });

                // Configure camera
                camera.position.set(30, 20, 30);
                camera.lookAt(0, 0, 0);

                // Fix OrbitControls initialization
                const controls = new window.OrbitControls(camera, renderer.domElement);
                controls.enableDamping = true;
                controls.dampingFactor = 0.05;
                controls.rotateSpeed = 0.5; // Slower rotation for more control
                controls.panSpeed = 0.5; // Slower panning
                controls.zoomSpeed = 0.8; // Slightly slower zoom

                // Set control limits
                controls.minDistance = 10; // Minimum zoom distance
                controls.maxDistance = 50; // Maximum zoom distance
                controls.maxPolarAngle = Math.PI / 2; // Limit vertical rotation to horizon
                controls.minPolarAngle = 0; // Limit vertical rotation looking up

                // Remove the viewButtons section and all related code

                // Add touch support for mobile
                let touchStartX, touchStartY;

                function onTouchStart(event) {
                    if (event.touches.length === 1) {
                        const touch = event.touches[0];
                        touchStartX = touch.clientX;
                        touchStartY = touch.clientY;
                    }
                }

                function onTouchMove(event) {
                    if (event.touches.length === 1) {
                        const touch = event.touches[0];

                        // Calculate rotation angles
                        const deltaX = (touch.clientX - touchStartX) * 0.01;
                        const deltaY = (touch.clientY - touchStartY) * 0.01;

                        // Update camera rotation
                        const rotationMatrix = new THREE.Matrix4();
                        rotationMatrix.makeRotationY(-deltaX);
                        const cameraPosition = new THREE.Vector3();
                        camera.getWorldPosition(cameraPosition);
                        cameraPosition.applyMatrix4(rotationMatrix);
                        camera.position.copy(cameraPosition);
                        camera.lookAt(scene.position);

                        // Store current position for next frame
                        touchStartX = touch.clientX;
                        touchStartY = touch.clientY;
                    }
                }

                // Add touch event listeners with proper options
                renderer.domElement.addEventListener('touchstart', onTouchStart, { passive: true });
                renderer.domElement.addEventListener('touchmove', onTouchMove, { passive: true });

                // Animation loop
                function animate() {
                    requestAnimationFrame(animate);
                    controls.update();

                    // Make coordinate labels face camera
                    if (window.coordinateLabels) {
                        window.coordinateLabels.forEach(label => {
                            label.lookAt(camera.position);
                        });
                    }

                    renderer.render(scene, camera);
                }

                function createLocation(scene, location) {
                    const container = new window.THREE.Group();
                    container.position.set(location.x_position, 0, location.z_position);

                    // Create enhanced floor with grid pattern
                    const floorSize = 7;
                    const floorGeometry = new window.THREE.PlaneGeometry(floorSize, floorSize);
                    const floorMaterial = new window.THREE.MeshStandardMaterial({
                        color: 0xe0e0e0,
                        metalness: 0.2,
                        roughness: 0.8
                    });
                    const floor = new window.THREE.Mesh(floorGeometry, floorMaterial);
                    floor.rotation.x = -Math.PI / 2;
                    container.add(floor);

                    // Add detailed grid
                    const gridHelper = new window.THREE.GridHelper(floorSize, 8, 0x888888, 0xcccccc);
                    gridHelper.position.y = 0.01;
                    container.add(gridHelper);

                    // Add floor border
                    const borderGeometry = new window.THREE.EdgesGeometry(new window.THREE.BoxGeometry(floorSize, 0.1, floorSize));
                    const borderMaterial = new window.THREE.LineBasicMaterial({ color: 0x2196f3, linewidth: 2 });
                    const border = new window.THREE.LineSegments(borderGeometry, borderMaterial);
                    border.position.y = 0.01;
                    container.add(border);

                    // Add location label with improved visuals
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.width = 512; // Increased resolution
                    canvas.height = 128;

                    // Create gradient background
                    const gradient = context.createLinearGradient(0, 0, 0, canvas.height);
                    gradient.addColorStop(0, '#2196f3');
                    gradient.addColorStop(1, '#1976d2');
                    context.fillStyle = gradient;
                    context.fillRect(0, 0, canvas.width, canvas.height);

                    // Add text with shadow
                    context.shadowColor = 'rgba(0, 0, 0, 0.5)';
                    context.shadowBlur = 4;
                    context.shadowOffsetX = 2;
                    context.shadowOffsetY = 2;
                    context.font = 'bold 48px Arial';
                    context.fillStyle = '#ffffff';
                    context.textAlign = 'center';
                    context.textBaseline = 'middle';
                    context.fillText(location.name, canvas.width / 2, canvas.height / 2);
                    context.fillStyle = 'rgba(255, 255, 255, 0.6)';
                    context.font = '24px Arial';
                    context.fillText(location.code, canvas.width / 2, canvas.height * 0.75);

                    const texture = new window.THREE.CanvasTexture(canvas);
                    const labelGeometry = new window.THREE.PlaneGeometry(2, 0.5);
                    const labelMaterial = new window.THREE.MeshBasicMaterial({
                        map: texture,
                        transparent: true,
                        side: window.THREE.DoubleSide,
                        depthWrite: false
                    });
                    const label = new window.THREE.Mesh(labelGeometry, labelMaterial);
                    label.position.set(0, 3, 0);
                    label.rotation.x = -Math.PI / 4;
                    container.add(label);

                    // Add corner pillars for visual reference
                    const pillarGeometry = new window.THREE.CylinderGeometry(0.1, 0.1, 0.5, 8);
                    const pillarMaterial = new window.THREE.MeshStandardMaterial({ color: 0x2196f3 });
                    const corners = [
                        [-floorSize / 2, floorSize / 2],
                        [floorSize / 2, floorSize / 2],
                        [-floorSize / 2, -floorSize / 2],
                        [floorSize / 2, -floorSize / 2]
                    ];

                    corners.forEach(([x, z]) => {
                        const pillar = new window.THREE.Mesh(pillarGeometry, pillarMaterial);
                        pillar.position.set(x, 0.25, z);
                        container.add(pillar);
                    });

                    // Create shelves
                    location.shelves.forEach((shelf, index) => {
                        // Attach location data to shelf
                        shelf.location = {
                            id: location.id,
                            name: location.name,
                            code: location.code
                        };
                        const shelfGroup = createShelf(shelf, index);
                        container.add(shelfGroup);
                    });

                    scene.add(container);

                    // Add highlight on hover
                    container.traverse((child) => {
                        if (child.isMesh) {
                            child.userData.locationName = location.name;
                            child.userData.locationCode = location.code;
                        }
                    });
                }

                function createShelf(shelf, index) {
                    const shelfGroup = new window.THREE.Group();
                    const currentLevel = parseInt(shelf.level) || 1;
                    const shelfItems = shelf.items.filter(item => item.location_code === shelf.location_code);

                    // Modern shelf dimensions
                    const dims = {
                        width: 2.2,      // Wider for better item display
                        height: 2.6,     // Taller overall height
                        depth: 0.8,      // Good depth for items
                        thickness: 0.03,  // Thinner, more modern look
                        spacing: 0.6,     // Level spacing
                        frames: 0.04     // Frame thickness
                    };

                    // Modern materials with metallic finish
                    const frameMaterial = new window.THREE.MeshStandardMaterial({
                        color: 0x303030,
                        metalness: 0.8,
                        roughness: 0.2,
                        envMapIntensity: 1
                    });

                    const shelfMaterial = new window.THREE.MeshStandardMaterial({
                        color: 0x404040,
                        metalness: 0.6,
                        roughness: 0.3,
                        envMapIntensity: 0.5
                    });

                    // Create vertical frames
                    const frameGeometry = new window.THREE.BoxGeometry(dims.frames, dims.height, dims.frames);
                    const corners = [
                        [-dims.width / 2, dims.height / 2, -dims.depth / 2],
                        [dims.width / 2, dims.height / 2, -dims.depth / 2],
                        [-dims.width / 2, dims.height / 2, dims.depth / 2],
                        [dims.width / 2, dims.height / 2, dims.depth / 2]
                    ];

                    corners.forEach(([x, y, z]) => {
                        const frame = new window.THREE.Mesh(frameGeometry, frameMaterial);
                        frame.position.set(x, y, z);
                        shelfGroup.add(frame);
                    });

                    // Create shelf levels
                    const levels = 4;
                    for (let i = 0; i < levels; i++) {
                        const yPos = i * dims.spacing;

                        // Create level group
                        const levelGroup = new window.THREE.Group();

                        // Main shelf surface with glass-like material
                        const shelfSurface = new window.THREE.Mesh(
                            new window.THREE.BoxGeometry(dims.width, dims.thickness, dims.depth),
                            new window.THREE.MeshPhysicalMaterial({
                                color: 0x888888,
                                metalness: 0.5,
                                roughness: 0.2,
                                transmission: 0.1,
                                thickness: 0.5
                            })
                        );

                        // Add reinforcement bars
                        const barGeometry = new window.THREE.BoxGeometry(dims.width, dims.frames, dims.frames);
                        const frontBar = new window.THREE.Mesh(barGeometry, frameMaterial);
                        const backBar = new window.THREE.Mesh(barGeometry, frameMaterial);

                        frontBar.position.set(0, -dims.frames / 2, dims.depth / 2 - dims.frames / 2);
                        backBar.position.set(0, -dims.frames / 2, -dims.depth / 2 + dims.frames / 2);

                        levelGroup.add(shelfSurface, frontBar, backBar);
                        levelGroup.position.y = yPos;
                        shelfGroup.add(levelGroup);

                        // Add items on current level
                        if (i + 1 === currentLevel) {
                            shelfItems.forEach((item, itemIndex) => {
                                const itemMesh = createInventoryItem(item);
                                const xOffset = -dims.width / 2 + 0.3 + (itemIndex * 0.4);
                                itemMesh.position.set(
                                    xOffset,
                                    yPos + 0.15,
                                    0
                                );
                                shelfGroup.add(itemMesh);
                            });
                        }

                        // Level indicator with modern design
                        const label = createLevelLabel(shelf.name, i + 1, currentLevel === i + 1);
                        label.position.set(
                            dims.width / 2 + 0.15,
                            yPos + 0.1,
                            dims.depth / 2
                        );
                        shelfGroup.add(label);
                    }

                    // Position shelf unit with more spacing
                    shelfGroup.position.set(
                        (index % 2) * 3.5 - 1.75,
                        0,
                        Math.floor(index / 2) * 3.5 - 1.75
                    );

                    // Add shelf data
                    shelfGroup.userData = {
                        type: 'shelf',
                        shelfData: {
                            ...shelf,
                            currentLevel,
                            items: shelfItems
                        }
                    };

                    return shelfGroup;
                }

                function createLevelLabel(shelfName, levelNum, isCurrentLevel) {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = 128;
                    canvas.height = 32;

                    // Highlight current level
                    ctx.fillStyle = isCurrentLevel ? '#e3f2fd' : '#ffffff';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    ctx.font = 'bold 20px Arial';
                    ctx.fillStyle = isCurrentLevel ? '#1976d2' : '#000000';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(`${shelfName}-${levelNum}`, 64, 16);

                    const texture = new window.THREE.CanvasTexture(canvas);
                    const geometry = new window.THREE.PlaneGeometry(0.4, 0.1);
                    const material = new window.THREE.MeshBasicMaterial({
                        map: texture,
                        transparent: true,
                        side: window.THREE.DoubleSide
                    });

                    return new window.THREE.Mesh(geometry, material);
                }

                function createInventoryItem(item) {
                    // Create a more detailed box for items
                    const itemGroup = new window.THREE.Group();

                    // Main box with beveled edges
                    const boxGeometry = new window.THREE.BoxGeometry(0.25, 0.25, 0.25);
                    const boxMaterial = new window.THREE.MeshPhysicalMaterial({
                        color: 0x4CAF50,
                        metalness: 0.4,
                        roughness: 0.6,
                        clearcoat: 0.5,
                        clearcoatRoughness: 0.2
                    });

                    const box = new window.THREE.Mesh(boxGeometry, boxMaterial);
                    itemGroup.add(box);

                    // Add edge highlights
                    const edgeGeometry = new window.THREE.EdgesGeometry(boxGeometry);
                    const edgeMaterial = new window.THREE.LineBasicMaterial({
                        color: 0x69F0AE,
                        transparent: true,
                        opacity: 0.5
                    });

                    const edges = new window.THREE.LineSegments(edgeGeometry, edgeMaterial);
                    itemGroup.add(edges);

                    // Update userData
                    itemGroup.userData = {
                        type: 'item',
                        name: item.item_name,
                        item_number: item.item_number,
                        batch_number: item.batch_number,
                        quantity: item.physical_inventory,
                        unit: item.bom_unit,
                        location_code: item.location_code,
                        level: parseInt(item.location_code.slice(-2, -1)) || 1
                    };

                    return itemGroup;
                }

                // Add raycaster for interactivity
                const raycaster = new window.THREE.Raycaster();
                const mouse = new window.THREE.Vector2();

                function onMouseMove(event) {
                    const rect = renderer.domElement.getBoundingClientRect();
                    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
                    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

                    raycaster.setFromCamera(mouse, camera);
                    const intersects = raycaster.intersectObjects(scene.children, true);

                    const infoPanel = document.getElementById('location-info');
                    if (intersects.length > 0) {
                        const object = intersects[0].object;
                        if (object.userData.type === 'item') {
                            infoPanel.textContent = `${object.userData.name} (${object.userData.sku}) - ${object.userData.quantity} ${object.userData.unit}`;
                            infoPanel.classList.remove('hidden');
                        } else if (object.userData.locationName) {
                            infoPanel.textContent = `Location: ${object.userData.locationName} (${object.userData.locationCode})`;
                            infoPanel.classList.remove('hidden');
                        }
                    } else {
                        infoPanel.classList.add('hidden');
                    }
                }

                renderer.domElement.addEventListener('mousemove', onMouseMove);

                // Add info panel to DOM
                const infoPanel = document.createElement('div');
                infoPanel.id = 'location-info';
                infoPanel.className = 'fixed top-4 right-4 bg-white dark:bg-gray-800 p-2 rounded shadow-lg hidden';
                document.getElementById('warehouse-container').appendChild(infoPanel);

                // Click handler
                function onClick(event) {
                    event.preventDefault();

                    const rect = renderer.domElement.getBoundingClientRect();
                    const mouse = new window.THREE.Vector2(
                        ((event.clientX - rect.left) / rect.width) * 2 - 1,
                        -((event.clientY - rect.top) / rect.height) * 2 + 1
                    );

                    raycaster.setFromCamera(mouse, camera);
                    const intersects = raycaster.intersectObjects(scene.children, true);

                    if (intersects.length > 0) {
                        let object = intersects[0].object;
                        let shelfGroup = null;

                        // Traverse up the parent hierarchy to find the shelf group
                        while (object && !object.userData.shelfData) {
                            object = object.parent;
                        }

                        if (object && object.userData.shelfData) {
                            const shelf = object.userData.shelfData;
                            showShelfModal(shelf);
                        }
                    }
                }

                function showShelfModal(shelf) {
                    const modal = document.getElementById('shelf-modal');
                    const title = document.getElementById('modal-title');
                    const content = document.getElementById('modal-content');

                    if (!shelf || !modal || !title || !content) {
                        console.error('Missing required elements for modal', { shelf, modal, title, content });
                        return;
                    }

                    title.textContent = `${shelf.name} - ${shelf.location?.name || 'Unknown Location'}`;

                    let html = `
                                                                <div class="space-y-4">
                                                                    <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
                                                                        <span>Code: ${shelf.code}</span>
                                                                        <span>Location Code: ${shelf.location_code}</span>
                                                                        <span>Capacity: ${shelf.capacity} units</span>
                                                                    </div>

                                                                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
                                                                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                                                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                                                                <tr>
                                                                                    <th scope="col" class="px-4 py-3">Item Number</th>
                                                                                    <th scope="col" class="px-4 py-3">Item Name</th>
                                                                                    <th scope="col" class="px-4 py-3">Batch No.</th>
                                                                                    <th scope="col" class="px-4 py-3">BOM Unit</th>
                                                                                    <th scope="col" class="px-4 py-3 text-right">Phys. Inv.</th>
                                                                                    <th scope="col" class="px-4 py-3 text-right">Reserved</th>
                                                                                    <th scope="col" class="px-4 py-3 text-right">Actual</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>`;

                    const items = shelf.items || [];
                    if (items.length > 0) {
                        items.forEach(item => {
                            html += `
                                                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                                            <td class="px-4 py-3">${item.item_number || '-'}</td>
                                                                            <td class="px-4 py-3 font-medium">${item.item_name || '-'}</td>
                                                                            <td class="px-4 py-3">${item.batch_number || '-'}</td>
                                                                            <td class="px-4 py-3">${item.bom_unit || '-'}</td>
                                                                            <td class="px-4 py-3 text-right">${item.physical_inventory || '0'}</td>
                                                                            <td class="px-4 py-3 text-right">${item.physical_reserved || '0'}</td>
                                                                            <td class="px-4 py-3 text-right">${item.actual_count || '0'}</td>
                                                                        </tr>`;
                        });
                    } else {
                        html += `
                                                                    <tr class="bg-white dark:bg-gray-800">
                                                                        <td colspan="8" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                                                            No items in this location
                                                                        </td>
                                                                    </tr>`;
                    }

                    html += `
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>`;

                    content.innerHTML = html;
                    modal.classList.remove('hidden');
                    document.addEventListener('keydown', window.handleEscapeKey);
                    modal.addEventListener('click', window.handleOutsideClick);
                }

                // Add click event listener to the renderer
                renderer.domElement.addEventListener('click', onClick);

                // Make cursor pointer when hovering over shelves
                renderer.domElement.addEventListener('mousemove', function (event) {
                    const rect = renderer.domElement.getBoundingClientRect();
                    const mouse = new window.THREE.Vector2(
                        ((event.clientX - rect.left) / rect.width) * 2 - 1,
                        -((event.clientY - rect.top) / rect.height) * 2 + 1
                    );

                    raycaster.setFromCamera(mouse, camera);
                    const intersects = raycaster.intersectObjects(scene.children, true);

                    if (intersects.length > 0) {
                        let object = intersects[0].object;
                        while (object && !object.userData.shelfData) {
                            object = object.parent;
                        }

                        renderer.domElement.style.cursor = object && object.userData.shelfData ? 'pointer' : 'default';
                    } else {
                        renderer.domElement.style.cursor = 'default';
                    }
                });

                // Close modal when clicking outside
                window.addEventListener('click', function (event) {
                    const modal = document.getElementById('shelf-modal');
                    if (event.target === modal) {
                        window.closeShelfModal();
                    }
                });

                // Handle window resize
                window.addEventListener('resize', () => {
                    camera.aspect = canvas.clientWidth / canvas.clientHeight;
                    camera.updateProjectionMatrix();
                    renderer.setSize(canvas.clientWidth, canvas.clientHeight, false);
                });

                // Start animation
                animate();
            });
        </script>
    @endpush

    @push('styles')
        <style>
            #shelf-modal {
                z-index: 100;
            }

            #shelf-modal .bg-gray-500\/75 {
                backdrop-filter: blur(4px);
            }

            #location-info {
                z-index: 1000;
                pointer-events: none;
            }

            .modal-open {
                overflow: hidden;
            }
        </style>
    @endpush
</x-filament::page>