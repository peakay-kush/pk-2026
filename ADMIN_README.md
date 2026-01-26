# Admin Panel - PK Automations

## Complete Admin Panel Features

### 📊 Dashboard
- Real-time statistics (products, orders, users, revenue)
- Pending and completed orders overview
- Low stock alerts
- Recent orders table
- Quick stats widget

### 📦 Products Management
- Add/Edit/Delete products
- Stock management
- Product categories
- Image upload
- Price and description management
- Low stock warnings

### 🛒 Orders Management
- View all orders
- Update order status (pending, processing, completed, cancelled)
- View order details
- Customer information
- Order items breakdown
- Payment status tracking

### 👥 Users Management
- View all users
- User roles (customer, admin)
- Delete/Edit users
- User activity tracking
- Account management

### 🔧 Services Management
- Add/Edit/Delete services
- Service title and description
- Icon management (Font Awesome)
- Service display control

### 🎓 Tutorials Management
- Add/Edit/Delete tutorials
- Video URL management (YouTube, Vimeo)
- Category organization
- Difficulty levels (beginner, intermediate, advanced)
- Tutorial descriptions

### 💳 Payments Management
- View all payments
- Payment status updates (pending, completed, failed)
- Payment method tracking (M-PESA, Card, Cash)
- Transaction history
- Payment statistics

### 📬 Messages Management
- View contact form messages
- Mark as read/unread
- Delete messages
- Customer inquiries

### 🔒 Security Features
- Admin authentication required
- Role-based access control
- Session management
- Secure password hashing

## 🚀 Access Information

**Admin Panel URL:** `http://localhost:8000/admin/login.php`

**Default Credentials:**
- **Email:** admin@pkautomations.com
- **Password:** admin123

**⚠️ Important:** Change the default password after first login!

## 📁 File Structure

```
admin/
├── index.php           # Dashboard
├── login.php           # Admin login
├── products.php        # Products management
├── orders.php          # Orders management
├── users.php           # Users management
├── services.php        # Services management
├── tutorials.php       # Tutorials management
├── payments.php        # Payments management
├── messages.php        # Messages management
├── header.php          # Admin header
├── footer.php          # Admin footer
└── includes/
    └── auth.php        # Authentication functions
```

## 🎨 Design Features

- Modern, clean interface
- Blue gradient sidebar
- Responsive design
- Bootstrap 5 components
- Font Awesome icons
- Hover effects and transitions
- Card-based layouts
- Color-coded status badges
- Sticky sidebar navigation

## 🛠️ Admin Capabilities

### Products
- ✅ Create new products
- ✅ Edit existing products
- ✅ Delete products
- ✅ Manage stock levels
- ✅ Set prices and descriptions
- ✅ Upload product images
- ✅ Low stock alerts

### Orders
- ✅ View all orders
- ✅ Update order status
- ✅ View customer details
- ✅ See order items
- ✅ Track payments
- ✅ Filter by status

### Users
- ✅ View all registered users
- ✅ Manage user roles
- ✅ Delete user accounts
- ✅ View user activity

### Services
- ✅ Add/Edit/Delete services
- ✅ Manage service descriptions
- ✅ Set Font Awesome icons
- ✅ Service display management

### Tutorials
- ✅ Add/Edit/Delete tutorials
- ✅ YouTube/Vimeo integration
- ✅ Category management
- ✅ Difficulty levels
- ✅ Tutorial descriptions

### Payments
- ✅ View payment history
- ✅ Update payment status
- ✅ Track payment methods
- ✅ Payment statistics
- ✅ Revenue tracking

## 📊 Statistics Available

- Total Products
- Total Orders
- Total Users
- Total Revenue
- Pending Orders
- Completed Orders
- Low Stock Items
- Total Services
- Total Tutorials

## 🔐 Security Notes

1. Admin authentication required for all pages
2. Session-based authentication
3. Password hashing with bcrypt
4. Role-based access control
5. Logout functionality

## 📝 Notes

- All admin pages have full CRUD operations
- Modal-based forms for adding/editing
- Confirmation dialogs for deletions
- Success/error messages
- Real-time statistics updates
- Responsive tables
- Search and filter capabilities
