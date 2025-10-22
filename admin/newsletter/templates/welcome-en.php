<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to TechEssentials Pro!</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px 20px;
        }
        
        .welcome-message {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 6px;
        }
        
        .benefits {
            background: #e7f3ff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .benefits h3 {
            color: #0066cc;
            margin-bottom: 15px;
        }
        
        .benefits ul {
            list-style: none;
            padding: 0;
        }
        
        .benefits li {
            padding: 8px 0;
            padding-left: 25px;
            position: relative;
        }
        
        .benefits li:before {
            content: "✅";
            position: absolute;
            left: 0;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
        }
        
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }
        
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        
        .social-links {
            margin: 15px 0;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #667eea;
            text-decoration: none;
        }
        
        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .content {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🎉 Welcome to TechEssentials Pro!</h1>
            <p>Your trusted guide for remote work technology</p>
        </div>
        
        <div class="content">
            <div class="welcome-message">
                <h2>Thank you for subscribing!</h2>
                <p>We're excited to have you join our community of 15,000+ remote workers who rely on our expert recommendations.</p>
            </div>
            
            <p>Hi there! 👋</p>
            
            <p>Welcome to the TechEssentials Pro newsletter family! You've just taken the first step towards optimizing your remote work setup with the best tech tools available.</p>
            
            <div class="benefits">
                <h3>Here's what you can expect from us:</h3>
                <ul>
                    <li><strong>Weekly tech deals</strong> - Exclusive discounts on top remote work tools</li>
                    <li><strong>Expert reviews</strong> - In-depth analysis of productivity gadgets</li>
                    <li><strong>Setup guides</strong> - Step-by-step tutorials for optimal workflows</li>
                    <li><strong>Early access</strong> - Be the first to know about new product launches</li>
                    <li><strong>Community insights</strong> - Tips and tricks from fellow remote workers</li>
                </ul>
            </div>
            
            <p>Our next newsletter goes out this Thursday with some amazing deals on webcams and microphones - perfect timing for your video calls!</p>
            
            <div style="text-align: center;">
                <a href="http://localhost/TechEssentialsPro/" class="cta-button">
                    🚀 Explore Our Latest Reviews
                </a>
            </div>
            
            <p>Got questions? Just reply to this email - we read every message and love hearing from our community.</p>
            
            <p>Happy remote working!<br>
            <strong>The TechEssentials Pro Team</strong></p>
        </div>
        
        <div class="footer">
            <div class="social-links">
                <a href="#">📧 Contact Us</a> |
                <a href="#">🌐 Website</a> |
                <a href="#">📱 Reviews</a>
            </div>
            
            <p>You're receiving this email because you subscribed to TechEssentials Pro newsletter.</p>
            <p>Email: {{email}}</p>
            
            <p style="margin-top: 15px;">
                <a href="{{unsubscribe_url}}" style="color: #999;">Unsubscribe</a> |
                <a href="#" style="color: #999;">Update Preferences</a>
            </p>
            
            <p style="margin-top: 10px; font-size: 0.8rem;">
                TechEssentials Pro - Best Tech for Remote Workers<br>
                © 2025 All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>