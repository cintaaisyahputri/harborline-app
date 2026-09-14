<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; background:#f4f6f8; padding:24px;">
    <table style="max-width:480px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;">
        <tr>
            <td style="background:#0f3d3e;color:#fff;padding:20px 24px;font-size:18px;font-weight:bold;">
                Harborline Provisions
            </td>
        </tr>
        <tr>
            <td style="padding:24px;color:#333;">
                <p>Hi {{ $name }},</p>
                <p>Use the code below to finish logging in. It expires in 10 minutes.</p>
                <p style="font-size:32px;font-weight:bold;letter-spacing:8px;text-align:center;
                          background:#f0f4f3;padding:16px;border-radius:6px;color:#0f3d3e;">
                    {{ $code }}
                </p>
                <p style="color:#888;font-size:13px;">
                    If you didn't try to log in, you can safely ignore this email.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
