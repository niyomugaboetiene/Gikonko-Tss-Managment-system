
<footer class="site-footer">

    <style>
        .site-footer {
            width: 100%;
            margin-top: 48px;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
        }

        .footer-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px;
        }

        .footer-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .footer-left,
        .footer-right {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .footer-right {
            text-align: right;
        }

        .footer-copyright {
            color: #334155;
            font-size: 13px;
            font-weight: 600;
        }

        .footer-description,
        .footer-credit {
            color: #64748b;
            font-size: 12px;
        }

        .footer-developer {
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
        }

        @media (max-width: 600px) {
            .footer-container {
                padding: 22px 15px;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
                gap: 18px;
            }

            .footer-left,
            .footer-right {
                align-items: center;
                text-align: center;
            }
        }
    </style>

    <div class="footer-container">

        <div class="footer-content">

            <div class="footer-left">
                <p class="footer-copyright">
                    &copy; <?php echo date("Y"); ?> GIKONKO TSS. All rights reserved.
                </p>

                <p class="footer-description">
                    Student Management System
                </p>
            </div>

            <div class="footer-right">
                <p class="footer-credit">
                    Designed & Developed by
                </p>

                <p class="footer-developer">
                    NIYOMUGABO Etiene
                </p>
            </div>

        </div>

    </div>

</footer>
