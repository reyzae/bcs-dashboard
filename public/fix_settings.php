<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Settings Error - Bytebalok Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 700px;
            width: 100%;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 40px;
        }
        
        .error-box {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .error-box h3 {
            color: #991b1b;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .error-box p {
            color: #7f1d1d;
            line-height: 1.6;
        }
        
        .solution-box {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .solution-box h3 {
            color: #065f46;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .solution-box p {
            color: #064e3b;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            text-align: center;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .steps {
            margin: 20px 0;
        }
        
        .step {
            display: flex;
            align-items: start;
            margin-bottom: 15px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
        }
        
        .step-number {
            background: #667eea;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-content strong {
            display: block;
            margin-bottom: 5px;
            color: #111827;
        }
        
        .step-content p {
            color: #6b7280;
            font-size: 14px;
            margin: 0;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .loading.active {
            display: block;
        }
        
        .spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .result {
            display: none;
            margin-top: 20px;
            padding: 20px;
            border-radius: 8px;
        }
        
        .result.success {
            display: block;
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        
        .result.error {
            display: block;
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }
        
        pre {
            background: #1f2937;
            color: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.5;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">⚙️</div>
            <h1>Perbaikan Error Settings</h1>
            <p>Fix HTTP 500 Error - Settings Table Not Found</p>
        </div>
        
        <div class="content">
            <div class="error-box">
                <h3>🔴 Error yang Terjadi:</h3>
                <p>
                    <strong>HTTP 500 - Failed to save general_settings</strong><br>
                    Tabel <code>settings</code> belum ada di database Anda. Tabel ini diperlukan untuk menyimpan konfigurasi sistem.
                </p>
            </div>
            
            <div class="solution-box">
                <h3>✅ Solusi:</h3>
                <p>
                    Klik tombol di bawah untuk membuat tabel <code>settings</code> secara otomatis. 
                    Proses ini akan:
                </p>
                
                <div class="steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <strong>Membuat Tabel Settings</strong>
                            <p>Struktur tabel baru akan dibuat di database</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <strong>Mengisi Data Default</strong>
                            <p>Setting default seperti currency, tax rate, dll akan diinput</p>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <strong>Siap Digunakan</strong>
                            <p>Setelah selesai, halaman settings akan berfungsi normal</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <button class="btn" id="runMigration" onclick="runMigration()">
                🚀 Jalankan Migrasi Database
            </button>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Sedang membuat tabel settings...</p>
            </div>
            
            <div id="result" class="result"></div>
        </div>
    </div>
    
    <script>
        async function runMigration() {
            const btn = document.getElementById('runMigration');
            const loading = document.getElementById('loading');
            const result = document.getElementById('result');
            
            // Hide button and show loading
            btn.style.display = 'none';
            loading.classList.add('active');
            result.style.display = 'none';
            
            try {
                const response = await fetch('run_migration.php');
                const text = await response.text();
                
                // Hide loading
                loading.classList.remove('active');
                
                if (response.ok && text.includes('completed successfully')) {
                    result.className = 'result success';
                    result.innerHTML = `
                        <h3>✅ Berhasil!</h3>
                        <p>Tabel settings telah dibuat dengan sukses.</p>
                        <pre>${text}</pre>
                        <p style="margin-top: 15px;">
                            <strong>Langkah selanjutnya:</strong><br>
                            1. Kembali ke halaman <a href="dashboard/settings.php" style="color: #667eea; font-weight: 600;">Settings</a><br>
                            2. Coba simpan pengaturan lagi<br>
                            3. Seharusnya error sudah teratasi! 🎉
                        </p>
                    `;
                } else if (text.includes('already exists')) {
                    result.className = 'result success';
                    result.innerHTML = `
                        <h3>ℹ️ Tabel Sudah Ada</h3>
                        <p>Tabel settings sudah ada di database Anda.</p>
                        <pre>${text}</pre>
                        <p style="margin-top: 15px;">
                            Jika masih ada error, silakan cek:<br>
                            1. Koneksi database<br>
                            2. Permission user database<br>
                            3. Browser console untuk error detail
                        </p>
                    `;
                } else {
                    throw new Error('Migration failed');
                }
            } catch (error) {
                loading.classList.remove('active');
                result.className = 'result error';
                result.innerHTML = `
                    <h3>❌ Error</h3>
                    <p>Gagal menjalankan migrasi. Detail error:</p>
                    <pre>${error.message}</pre>
                    <p style="margin-top: 15px;">
                        Solusi alternatif:<br>
                        1. Buka phpMyAdmin atau MySQL client lainnya<br>
                        2. Pilih database <strong>bytebalok_dashboard</strong><br>
                        3. Jalankan file <strong>database_settings_table.sql</strong> secara manual
                    </p>
                `;
                btn.style.display = 'block';
            }
            
            result.style.display = 'block';
        }
    </script>
</body>
</html>

