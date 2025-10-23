<?php
include('conecta.php');

if (!file_exists('conecta.php')) {
    die("Erro: Arquivo de conexão não encontrado");
}

if (!isset($host) || !isset($nomedobanco) || !isset($usuario) || !isset($senha)) {
    die("Erro: Variáveis de conexão não definidas no conecta.php");
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$nomedobanco;charset=utf8mb4", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro de conexão: " . $e->getMessage());
    $erro_conexao = "Erro ao conectar ao banco de dados.";
}

$mensagem_enviada = false;
$erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    if (!isset($pdo) || !$pdo) {
        $erro = "Erro de conexão com o banco de dados. Tente novamente.";
    } else {

        $mensagem = filter_input(INPUT_POST, 'mensagem', FILTER_SANITIZE_STRING);
        $mensagem = trim($mensagem ?? '');

        if (!empty($mensagem)) {
            try {
               
                $stmt = $pdo->query("DESCRIBE suporte");
                $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
               
                if (in_array('_mensagem', $colunas) && in_array('data_envio', $colunas)) {
                   
                    $sql = "INSERT INTO suporte (_mensagem, data_envio) VALUES (:_mensagem, NOW())";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':_mensagem' => $mensagem]);
                    
                } elseif (in_array('mensagem', $colunas) && in_array('data_criacao', $colunas)) {
                   
                    $sql = "INSERT INTO suporte (mensagem, data_criacao) VALUES (:mensagem, NOW())";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':mensagem' => $mensagem]);
                    
                } else {
                   
                    if (in_array('_mensagem', $colunas)) {
                        $sql = "INSERT INTO suporte (_mensagem) VALUES (:_mensagem)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([':_mensagem' => $mensagem]);
                    } elseif (in_array('mensagem', $colunas)) {
                        $sql = "INSERT INTO suporte (mensagem) VALUES (:mensagem)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([':mensagem' => $mensagem]);
                    } else {
                        throw new Exception("Estrutura da tabela não reconhecida");
                    }
                }
                
                $mensagem_enviada = true;
                $mensagem = ''; 
                
            } catch (PDOException $e) {
                error_log("Erro ao inserir mensagem: " . $e->getMessage());
                $erro = "Erro ao enviar mensagem. Tente novamente.";
                
                if (strpos($e->getMessage(), 'Column') !== false) {
                    $erro = "Erro: Problema com a estrutura da tabela.";
                }
            } catch (Exception $e) {
                error_log("Erro geral: " . $e->getMessage());
                $erro = "Erro ao processar sua mensagem.";
            }
        } else {
            $erro = "Por favor, digite uma mensagem.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Suporte - Gestão Escolar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 600px;
        }

        .form-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h1 {
            color: #1a237e;
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .form-header p {
            color: #666;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1a237e;
            font-size: 1rem;
        }

        .form-textarea {
            width: 100%;
            min-height: 200px;
            padding: 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-family: inherit;
            font-size: 16px;
            resize: vertical;
            transition: all 0.3s ease;
            background-color: #fafbfc;
            line-height: 1.5;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #1a237e;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
            transform: translateY(-2px);
        }

        .form-textarea::placeholder {
            color: #9aa0a6;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #1a237e, #303f9f);
            color: white;
            padding: 16px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #303f9f, #1a237e);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 35, 126, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .debug-info {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            font-size: 0.9rem;
            border: 1px solid #ffeaa7;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .form-card {
                padding: 30px 20px;
                margin: 10px;
            }

            .form-header h1 {
                font-size: 1.7rem;
            }

            .form-textarea {
                min-height: 150px;
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <h1>Suporte Técnico</h1>
                <p>Descreva sua dúvida ou problema abaixo</p>
            </div>

            <?php if (isset($erro_conexao)): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($erro_conexao); ?>
                </div>
            <?php endif; ?>

            <?php if ($mensagem_enviada): ?>
                <div class="alert alert-success">
                    ✅ Mensagem enviada com sucesso! Entraremos em contato em breve.
                </div>
            <?php elseif ($erro): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="mensagem" class="form-label">Mensagem:</label>
                    <textarea 
                        name="mensagem" 
                        id="mensagem" 
                        class="form-textarea" 
                        rows="8" 
                        placeholder="Digite detalhadamente sua dúvida, problema ou sugestão..."
                        required
                    ><?php echo isset($mensagem) ? htmlspecialchars($mensagem) : ''; ?></textarea>
                </div>

                <button type="submit" class="btn-submit" <?php echo isset($erro_conexao) ? 'disabled' : ''; ?>>
                    📨 Enviar Mensagem
                </button>
            </form>

          
            <?php if (isset($pdo) && $pdo): ?>
                <div class="debug-info" style="margin-top: 20px; display: none;" id="debugInfo">
                    <strong>Informações de Debug:</strong><br>
                    - Banco: <?php echo htmlspecialchars($nomedobanco); ?><br>
                    - Tabela: suporte<br>
                    - Colunas encontradas: <?php 
                        try {
                            $stmt = $pdo->query("DESCRIBE suporte");
                            $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            echo implode(', ', $colunas);
                        } catch (Exception $e) {
                            echo 'Erro ao verificar colunas';
                        }
                    ?><br>
                    - Estrutura usada: <?php 
                        if (isset($colunas)) {
                            if (in_array('_mensagem', $colunas)) {
                                echo '_mensagem + data_envio';
                            } elseif (in_array('mensagem', $colunas)) {
                                echo 'mensagem + data_criacao';
                            } else {
                                echo 'Não reconhecida';
                            }
                        }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('mensagem').focus();
            
            
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'd') {
                    e.preventDefault();
                    const debug = document.getElementById('debugInfo');
                    if (debug) debug.style.display = debug.style.display === 'none' ? 'block' : 'none';
                }
            });
        });

        <?php if ($mensagem_enviada): ?>
            setTimeout(function() {
                const alert = document.querySelector('.alert-success');
                if (alert) {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => alert.remove(), 500);
                }
            }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>