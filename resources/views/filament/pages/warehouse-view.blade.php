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
                    <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl sm:p-6">
                        <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                            <button type="button" onclick="closeShelfModal()" class="rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500 focus:outline-none mb-4">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 id="modal-title" class="text-xl font-semibold leading-6 text-gray-900 dark:text-gray-100"></h3>
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
            window.closeShelfModal = function() {
                const modal = document.getElementById('shelf-modal');
                if (modal) {
                    modal.classList.add('hidden');
                }
            };

            window.handleOutsideClick = function(event) {
                const modal = document.getElementById('shelf-modal');
                const modalContent = modal.querySelector('.rounded-lg');
                if (event.target === modal) {
                    closeShelfModal();
                }
            };

            window.handleEscapeKey = function(event) {
                if (event.key === 'Escape') {
                    closeShelfModal();
                }
            };

            document.addEventListener('DOMContentLoaded', function() {
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

                // Position camera
                camera.position.set(20, 20, 20);
                camera.lookAt(0, 0, 0);

                // Add OrbitControls
                const controls = new THREE.OrbitControls(camera, renderer.domElement);
                controls.enableDamping = true;
                controls.dampingFactor = 0.05;

                // Animation loop
                function animate() {
                    requestAnimationFrame(animate);
                    controls.update();
                    renderer.render(scene, camera);
                }

                function createLocation(scene, location) {
                    // Create location container
                    const container = new THREE.Group();
                    container.position.set(location.x_position, 0, location.z_position);

                    // Create floor for this location
                    const floorGeometry = new THREE.PlaneGeometry(4, 4);
                    const floorMaterial = new THREE.MeshStandardMaterial({ color: 0xcccccc });
                    const floor = new THREE.Mesh(floorGeometry, floorMaterial);
                    floor.rotation.x = -Math.PI / 2;
                    container.add(floor);

                    // Add location label
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    canvas.width = 256;
                    canvas.height = 64;
                    context.fillStyle = '#ffffff';
                    context.fillRect(0, 0, canvas.width, canvas.height);
                    context.font = 'bold 24px Arial';
                    context.fillStyle = '#000000';
                    context.textAlign = 'center';
                    context.textBaseline = 'middle';
                    context.fillText(location.name, canvas.width / 2, canvas.height / 2);

                    const texture = new THREE.CanvasTexture(canvas);
                    const labelGeometry = new THREE.PlaneGeometry(2, 0.5);
                    const labelMaterial = new THREE.MeshBasicMaterial({
                        map: texture,
                        transparent: true,
                        side: THREE.DoubleSide
                    });
                    const label = new THREE.Mesh(labelGeometry, labelMaterial);
                    label.position.set(0, 2.5, 0); // Position above the shelves
                    label.rotation.x = -Math.PI / 4; // Tilt for better visibility
                    container.add(label);

                    // Create shelves with location reference
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

                    // Create shelf base structure
                    const baseGeometry = new THREE.BoxGeometry(1.5, 0.1, 0.8);
                    const baseMaterial = new THREE.MeshStandardMaterial({ color: 0x606060 });

                    // Create vertical supports
                    const supportGeometry = new THREE.BoxGeometry(0.1, 2, 0.8);
                    const supportMaterial = new THREE.MeshStandardMaterial({ color: 0x404040 });

                    // Create shelf levels
                    const levels = 4;
                    const levelHeight = 0.5;

                    // Add vertical supports
                    const leftSupport = new THREE.Mesh(supportGeometry, supportMaterial);
                    const rightSupport = new THREE.Mesh(supportGeometry, supportMaterial);
                    leftSupport.position.set(-0.7, 1, 0);
                    rightSupport.position.set(0.7, 1, 0);
                    shelfGroup.add(leftSupport);
                    shelfGroup.add(rightSupport);

                    // Add shelf levels
                    for (let i = 0; i < levels; i++) {
                        const levelMesh = new THREE.Mesh(baseGeometry, baseMaterial);
                        levelMesh.position.set(0, i * levelHeight, 0);
                        shelfGroup.add(levelMesh);

                        // Add shelf label
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
                        label.position.set(0.8, i * levelHeight + 0.1, 0.41);
                        shelfGroup.add(label);

                        // Add items on this level if they exist
                        const levelItems = shelf.items.filter(item => item.shelf_position === i + 1);
                        levelItems.forEach((item, itemIndex) => {
                            const itemMesh = createInventoryItem(item);
                            itemMesh.position.set(
                                -0.5 + (itemIndex * 0.25),
                                i * levelHeight + 0.15,
                                0
                            );
                            shelfGroup.add(itemMesh);
                        });
                    }

                    // Position the entire shelf unit
                    shelfGroup.position.set(
                        (index % 2) * 2 - 1,
                        0,
                        Math.floor(index / 2) * 2 - 1
                    );

                    // Update userData to include full shelf data with location
                    shelfGroup.userData = {
                        type: 'shelf',
                        shelfData: shelf // shelf now includes location data
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

                    // Add item hover data
                    itemMesh.userData = {
                        type: 'item',
                        name: item.name,
                        sku: item.sku,
                        quantity: item.quantity,
                        unit: item.unit
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

                    console.log('Showing modal for shelf:', shelf); // Debug log

                    title.textContent = `${shelf.name} - ${shelf.location?.name || 'Unknown Location'}`;

                    let html = `
                        <div class="space-y-4">
                            <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
                                <span>Code: ${shelf.code}</span>
                                <span>Level: ${shelf.level}</span>
                                <span>Capacity: ${shelf.capacity} units</span>
                            </div>
                            
                            <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
                                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                    <thead class="text  -xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                        <tr>
                                            <th scope="col" class="px-6 py-3">Item Name</th>
                                            <th scope="col" class="px-6 py-3">SKU</th>
                                            <th scope="col" class="px-6 py-3">Description</th>
                                            <th scope="col" class="px-6 py-3">Position</th>
                                            <th scope="col" class="px-6 py-3 text-right">Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

                    const items = shelf.items || [];
                    if (items.length > 0) {
                        items.forEach(item => {
                            html += `
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                        ${item.name}
                                    </th>
                                    <td class="px-6 py-4">${item.sku}</td>
                                    <td class="px-6 py-4">${item.description || '-'}</td>
                                    <td class="px-6 py-4">Level ${item.shelf_position}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            ${item.quantity} ${item.unit}
                                        </span>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html += `
                            <tr class="bg-white dark:bg-gray-800">
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No items on this shelf
                                </td>
                            </tr>
                        `;
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