<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: /mywebsite/app/views/authentication.php");
    exit();
}

$user = $_SESSION['user'];
$order = [];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    
    <link href="../../public/css/node_modules/bootstrap/dist/css/bootstrap.css" rel="stylesheet">
    
    <style>
        .account-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .user-info {
            padding: 20px;
            margin-bottom: 20px;
            background-color: white;
            border-radius: 6px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        }
        
        .order-history {
            padding: 20px;
            background-color: white;
            border-radius: 6px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        }
        
        .nav-tabs {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="container account-container">
        <div class="row mb-4">
            <div class="col">
                <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
            </div>
            <div class="col-auto">
                <a href="/mywebsite/public/index.php?page=logout" class="btn btn-outline-danger">Logout</a>
            </div>
        </div>
        
        <ul class="nav nav-tabs" id="accountTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">Profile</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-controls="orders" aria-selected="false">Order History</button>
            </li>
        </ul>
        
        <div class="tab-content" id="accountTabContent">
            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <div class="user-info">
                    <h3>My Profile</h3>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                            <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-pane fade" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                <div class="order-history">
                    <h3>Order History</h3>
                    <hr>
                    
                    <?php if (empty($orders)): ?>
                        <div class="alert alert-info">
                            You haven't placed any orders yet.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo $order['id']; ?></td>
                                        <td><?php echo date('m/d/Y', strtotime($order['date'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo getStatusColor($order['status']); ?>">
                                                <?php echo $order['status']; ?>
                                            </span>
                                        </td>
                                        <td>$<?php echo number_format($order['total'], 2); ?></td>
                                        <td>
                                            <a href="/mywebsite/public/index.php?page=order&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php

    function getStatusColor($status) {
        switch(strtolower($status)) {
            case 'completed':
                return 'success';
            case 'processing':
                return 'primary';
            case 'pending':
                return 'warning';
            case 'cancelled':
                return 'danger';
            default:
                return 'secondary';
        }
    }
    ?>
</body>
</html>