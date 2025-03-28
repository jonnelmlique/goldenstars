<x-filament::page>
    <div class="space-y-6">
        <div id="warehouse-container" class="w-full h-[600px] bg-gray-100 dark:bg-gray-900 rounded-lg">
            <canvas id="three-canvas" class="w-full h-full"></canvas>
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
                        <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                            <button type="button" onclick="closeShelfModal()"
                                class="rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500 focus:outline-none mb-4">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 id="modal-title"
                                    class="text-xl font-semibold leading-6 text-gray-900 dark:text-gray-100"></h3>
                                <div id="modal-content" class="mt-4"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
        <script>
            // Make these functions globally available
            window.closeShelfModal = function () {
                const modal = document.getElementById('shelf-modal');
                if (modal) {
                    modal.classList.add('hidden');
                }
            };

            window.handleOutsideClick = function (event) {
                const modal = document.getElementById('shelf-modal');
                const modalContent = modal.querySelector('.rounded-lg');
                if (event.target === modal) {
                    closeShelfModal();
                }
            };

            window.handleEscapeKey = function (event) {
                if (event.key === 'Escape') {
                    closeShelfModal();
                }
            };

            document.addEventListener('DOMContentLoaded', function () {
                const canvas = document.getElementById('three-canvas');
                const scene = new THREE.Scene();
                const camera = new THREE.PerspectiveCamera(75, canvas.clientWidth / canvas.clientHeight, 0.1, 1000);
                const renderer = new THREE.WebGLRenderer({
                    canvas: canvas,
                    antialias: true
                });

                renderer.setSize(canvas.clientWidth, canvas.clientHeight);
                renderer.setClearColor(0xf0f0f0);

                // Add lighting
                const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
                scene.add(ambientLight);

                const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
                directionalLight.position.set(10, 10, 10);
                scene.add(directionalLight);

                // Create warehouse locations
                const locations = @json($locations);
                locations.forEach(location => {
                    createLocation(scene, location);
                });

                // Configure camera
                camera.position.set(30, 20, 30);
                camera.lookAt(0, 0, 0);

                // Enhanced OrbitControls configuration
                const controls = new THREE.OrbitControls(camera, renderer.domElement);
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
                renderer.domElement.addEventListener('touchstart', onTouchStart, false);
                renderer.domElement.addEventListener('touchmove', onTouchMove, false);

                let touchStart = new THREE.Vector2();

                function onTouchStart(event) {
                    event.preventDefault();
                    const touch = event.touches[0];
                    touchStart.set(touch.clientX, touch.clientY);
                }

                function onTouchMove(event) {
                    event.preventDefault();
                    const touch = event.touches[0];
                    const deltaX = touch.clientX - touchStart.x;
                    const deltaY = touch.clientY - touchStart.y;

                    touchStart.set(touch.clientX, touch.clientY);
                    controls.rotateLeft(deltaX * 0.005);
                    controls.rotateUp(deltaY * 0.005);
                }

                // Animation loop
                function animate() {
                    requestAnimationFrame(animate);
                    controls.update();
                    renderer.render(scene, camera);
                }

                function createLocation(scene, location) {
                    const container = new THREE.Group();
                    container.position.set(location.x_position, 0, location.z_position);

                    // Create enhanced floor with grid pattern
                    const floorSize = 4;
                    const floorGeometry = new THREE.PlaneGeometry(floorSize, floorSize);
                    const floorMaterial = new THREE.MeshStandardMaterial({
                        color: 0xe0e0e0,
                        metalness: 0.2,
                        roughness: 0.8
                    });
                    const floor = new THREE.Mesh(floorGeometry, floorMaterial);
                    floor.rotation.x = -Math.PI / 2;
                    container.add(floor);

                    // Add detailed grid
                    const gridHelper = new THREE.GridHelper(floorSize, 8, 0x888888, 0xcccccc);
                    gridHelper.position.y = 0.01;
                    container.add(gridHelper);

                    // Add floor border
                    const borderGeometry = new THREE.EdgesGeometry(new THREE.BoxGeometry(floorSize, 0.1, floorSize));
                    const borderMaterial = new THREE.LineBasicMaterial({ color: 0x2196f3, linewidth: 2 });
                    const border = new THREE.LineSegments(borderGeometry, borderMaterial);
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

                    const texture = new THREE.CanvasTexture(canvas);
                    const labelGeometry = new THREE.PlaneGeometry(2, 0.5);
                    const labelMaterial = new THREE.MeshBasicMaterial({
                        map: texture,
                        transparent: true,
                        side: THREE.DoubleSide,
                        depthWrite: false
                    });
                    const label = new THREE.Mesh(labelGeometry, labelMaterial);
                    label.position.set(0, 3, 0);
                    label.rotation.x = -Math.PI / 4;
                    container.add(label);

                    // Add corner pillars for visual reference
                    const pillarGeometry = new THREE.CylinderGeometry(0.1, 0.1, 0.5, 8);
                    const pillarMaterial = new THREE.MeshStandardMaterial({ color: 0x2196f3 });
                    const corners = [
                        [-floorSize / 2, floorSize / 2],
                        [floorSize / 2, floorSize / 2],
                        [-floorSize / 2, -floorSize / 2],
                        [floorSize / 2, -floorSize / 2]
                    ];

                    corners.forEach(([x, z]) => {
                        const pillar = new THREE.Mesh(pillarGeometry, pillarMaterial);
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
                    const shelfGroup = new THREE.Group();

                    // Adjusted dimensions for better stability
                    const baseGeometry = new THREE.BoxGeometry(1.5, 0.05, 0.8);
                    const baseMaterial = new THREE.MeshStandardMaterial({
                        color: 0x606060,
                        metalness: 0.3,
                        roughness: 0.7
                    });

                    // Thinner supports with proper height
                    const supportGeometry = new THREE.BoxGeometry(0.05, 2, 0.05);
                    const supportMaterial = new THREE.MeshStandardMaterial({
                        color: 0x404040,
                        metalness: 0.5,
                        roughness: 0.5
                    });

                    // Create shelf levels with precise spacing
                    const levels = 4;
                    const levelSpacing = 0.5; // Consistent spacing
                    const totalHeight = levelSpacing * (levels - 1);

                    // Add supports at each corner
                    const supportPositions = [
                        { x: -0.7, z: -0.35 }, // Front left
                        { x: 0.7, z: -0.35 },  // Front right
                        { x: -0.7, z: 0.35 },  // Back left
                        { x: 0.7, z: 0.35 }    // Back right
                    ];

                    supportPositions.forEach(pos => {
                        const support = new THREE.Mesh(supportGeometry, supportMaterial);
                        support.position.set(pos.x, 1, pos.z);
                        shelfGroup.add(support);
                    });

                    // Add shelf levels with precise positioning
                    for (let i = 0; i < levels; i++) {
                        const levelMesh = new THREE.Mesh(baseGeometry, baseMaterial);
                        const yPosition = i * levelSpacing;
                        levelMesh.position.set(0, yPosition, 0);
                        shelfGroup.add(levelMesh);

                        // Add shelf label with adjusted position
                        const labelCanvas = document.createElement('canvas');
                        const ctx = labelCanvas.getContext('2d');
                        labelCanvas.width = 128;
                        labelCanvas.height = 32;
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, 128, 32);
                        ctx.font = 'bold 20px Arial';
                        ctx.fillStyle = '#000000';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(`${shelf.name}-${i + 1}`, 64, 16);

                        const labelTexture = new THREE.CanvasTexture(labelCanvas);
                        const labelGeometry = new THREE.PlaneGeometry(0.4, 0.1);
                        const labelMaterial = new THREE.MeshBasicMaterial({
                            map: labelTexture,
                            transparent: true,
                            side: THREE.DoubleSide
                        });
                        const label = new THREE.Mesh(labelGeometry, labelMaterial);
                        label.position.set(0.8, yPosition + 0.1, 0.41);
                        shelfGroup.add(label);

                        // Add items with proper filtering by location_code
                        const levelItems = shelf.items.filter(item => item.location_code === shelf.location_code);
                        levelItems.forEach((item, itemIndex) => {
                            const itemMesh = createInventoryItem(item);
                            itemMesh.position.set(
                                -0.5 + (itemIndex * 0.25),
                                yPosition + 0.075,
                                0
                            );
                            shelfGroup.add(itemMesh);
                        });
                    }

                    // Rest of the existing shelf creation code
                    shelfGroup.position.set(
                        (index % 2) * 2 - 1,
                        0,
                        Math.floor(index / 2) * 2 - 1
                    );

                    // Update userData to include full shelf data with location
                    shelfGroup.userData = {
                        type: 'shelf',
                        shelfData: {
                            ...shelf,
                            items: shelf.items.filter(item => item.location_code === shelf.location_code)
                        }
                    };

                    return shelfGroup;
                }

                function createInventoryItem(item) {
                    const itemGeometry = new THREE.BoxGeometry(0.2, 0.2, 0.2);
                    const itemMaterial = new THREE.MeshStandardMaterial({
                        color: 0x4CAF50,
                        metalness: 0.5,
                        roughness: 0.7
                    });
                    const itemMesh = new THREE.Mesh(itemGeometry, itemMaterial);

                    // Update item userData
                    itemMesh.userData = {
                        type: 'item',
                        name: item.name,
                        sku: item.sku,
                        quantity: item.quantity,
                        unit: item.unit,
                        location_code: item.location_code
                    };

                    return itemMesh;
                }

                // Add raycaster for interactivity
                const raycaster = new THREE.Raycaster();
                const mouse = new THREE.Vector2();

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
                    const mouse = new THREE.Vector2(
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

                    // Add event listeners
                    document.addEventListener('keydown', window.handleEscapeKey);
                    modal.addEventListener('click', window.handleOutsideClick);
                }

                function closeShelfModal() {
                    const modal = document.getElementById('shelf-modal');
                    if (modal) {
                        modal.classList.add('hidden');
                        // Remove any existing event listeners
                        window.removeEventListener('click', handleOutsideClick);
                        window.removeEventListener('keydown', handleEscapeKey);
                    }
                }

                function handleOutsideClick(event) {
                    const modal = document.getElementById('shelf-modal');
                    const modalContent = modal.querySelector('.rounded-lg');
                    if (event.target === modal || !modalContent.contains(event.target)) {
                        closeShelfModal();
                    }
                }

                function handleEscapeKey(event) {
                    if (event.key === 'Escape') {
                        closeShelfModal();
                    }
                }

                // Add click event listener to the renderer
                renderer.domElement.addEventListener('click', onClick);

                // Make cursor pointer when hovering over shelves
                renderer.domElement.addEventListener('mousemove', function (event) {
                    const rect = renderer.domElement.getBoundingClientRect();
                    const mouse = new THREE.Vector2(
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
                        closeShelfModal();
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