<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/WishlistController.php';
require_once __DIR__ . '/../controllers/OrderController.php';

class Router {
    public static function handle($method, $uri, $data) {
        $uri = explode('?', $uri)[0]; // Remove query params for routing
        $uriSegments = explode('/', trim($uri, '/'));

        // Allow matching either /api/... or /clothing-rental-api/api/...
        // depending on how XAMPP rewrites it
        $apiIndex = array_search('api', $uriSegments);
        
        if ($apiIndex !== false) {
            $base = $apiIndex;
            
            // --- AUTH ---
            if (isset($uriSegments[$base+1]) && $uriSegments[$base+1] === 'register' && $method === 'POST') {
                $controller = new AuthController();
                $controller->register($data);
                return;
            }
            if (isset($uriSegments[$base+1]) && $uriSegments[$base+1] === 'login' && $method === 'POST') {
                $controller = new AuthController();
                $controller->login($data);
                return;
            }

            // --- CATEGORIES ---
            if (isset($uriSegments[$base+1]) && $uriSegments[$base+1] === 'categories') {
                $controller = new CategoryController();
                if (!isset($uriSegments[$base+2]) && $method === 'GET') {
                    $controller->getAll();
                    return;
                }
                if (isset($uriSegments[$base+2]) && isset($uriSegments[$base+3]) && $uriSegments[$base+3] === 'products' && $method === 'GET') {
                    $controller->getProductsByCategory($uriSegments[$base+2]);
                    return;
                }
            }

            // --- PRODUCTS ---
            if (isset($uriSegments[$base+1]) && $uriSegments[$base+1] === 'products') {
                $controller = new ProductController();
                
                if (isset($uriSegments[$base+2]) && $uriSegments[$base+2] === 'search' && $method === 'GET') {
                    $controller->search($_GET);
                    return;
                }
                if (!isset($uriSegments[$base+2]) && $method === 'GET') {
                    $controller->getAll();
                    return;
                }
                if (!isset($uriSegments[$base+2]) && $method === 'POST') {
                    $controller->create($data);
                    return;
                }
                if (isset($uriSegments[$base+2]) && !isset($uriSegments[$base+3])) {
                    if ($method === 'GET') {
                        $controller->getOne($uriSegments[$base+2]);
                        return;
                    }
                    if ($method === 'PUT') {
                        $controller->update($uriSegments[$base+2], $data);
                        return;
                    }
                    if ($method === 'DELETE') {
                        $controller->delete($uriSegments[$base+2]);
                        return;
                    }
                }
            }

            // --- WISHLIST ---
            if (isset($uriSegments[$base+1]) && $uriSegments[$base+1] === 'wishlist') {
                $controller = new WishlistController();
                if (!isset($uriSegments[$base+2])) {
                    if ($method === 'POST') {
                        $controller->create($data);
                        return;
                    }
                    if ($method === 'GET') {
                        $controller->getByUser();
                        return;
                    }
                }
                if (isset($uriSegments[$base+2]) && $method === 'DELETE') {
                    $controller->delete($uriSegments[$base+2]);
                    return;
                }
            }

            // --- ORDERS ---
            if (isset($uriSegments[$base+1]) && $uriSegments[$base+1] === 'orders') {
                $controller = new OrderController();
                if (!isset($uriSegments[$base+2])) {
                    if ($method === 'POST') {
                        $controller->create($data);
                        return;
                    }
                    if ($method === 'GET') {
                        $controller->getByUser();
                        return;
                    }
                }
                if (isset($uriSegments[$base+2])) {
                    if (!isset($uriSegments[$base+3]) && $method === 'GET') {
                        $controller->getOne($uriSegments[$base+2]);
                        return;
                    }
                    if (isset($uriSegments[$base+3]) && $uriSegments[$base+3] === 'status' && $method === 'PUT') {
                        $controller->updateStatus($uriSegments[$base+2], $data);
                        return;
                    }
                }
            }
        }

        Response::error("Endpoint not found", [], 404);
    }
}
?>
