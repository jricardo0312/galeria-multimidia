<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Álbuns de Fotos e Vídeos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

    <header class="bg-white shadow-sm border-b border-gray-200 py-4 px-6 flex justify-between items-center">
        <h1 class="text-xl font-bold text-gray-900 tracking-tight"><a href="index.php">📸 Meus Álbuns Multimídia</a></h1>
        <div>
            <?php if ($isAdmin): ?>
                <span class="text-sm bg-green-100 text-green-800 px-3 py-1.5 rounded-full font-medium mr-2">Modo Admin</span>
                <a href="?logout=1" class="text-sm text-red-600 hover:text-red-800 font-semibold transition">Sair</a>
            <?php elseif ($isLoggedIn): ?>
                <span class="text-sm bg-indigo-100 text-indigo-800 px-3 py-1.5 rounded-full font-medium mr-2">Área de Amigos</span>
                <a href="?logout=1" class="text-sm text-red-600 hover:text-red-800 font-semibold transition">Sair</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-8">

        <?php if (!$isLoggedIn): ?>
            <div class="max-w-md mx-auto bg-white rounded-xl shadow-md border border-gray-100 p-6 my-8">
                <h2 class="text-lg font-bold mb-4 text-center">Autenticação do Sistema</h2>
                <?php if ($error): ?>
                    <p class="text-red-500 text-sm mb-3 bg-red-50 p-2 rounded text-center"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="login" value="1">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Usuário</label>
                        <input type="text" name="user" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Senha</label>
                        <input type="password" name="pass" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-lg transition shadow-sm">Entrar</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <div class="max-w-4xl mx-auto bg-white border border-gray-200 rounded-xl p-6 mb-10 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="border-r-0 md:border-r md:border-gray-100 pr-0 md:pr-6">
                    <h3 class="font-bold text-gray-800 mb-3 text-sm uppercase tracking-wider">Criar Novo Álbum</h3>
                    <form method="POST" class="flex gap-2">
                        <input type="hidden" name="criar_album" value="1">
                        <input type="text" name="nome_album" placeholder="Ex: Viagem 2026" required class="w-full text-sm px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap">Criar</button>
                    </form>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 mb-3 text-sm uppercase tracking-wider">Enviar fotos/vídeos para: <span class="text-indigo-600"><?= htmlspecialchars($album_atual['nome'] ?? 'Nenhum álbum') ?></span></h3>
                    <?php if ($album_atual): ?>
                        <form method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-2">
                            <input type="hidden" name="upload" value="1">
                            <input type="file" name="arquivos[]" accept="image/*,video/*" multiple required class="text-sm w-full file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                            <button type="submit" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap">Enviar Todos</button>
                        </form>
                    <?php else: ?>
                        <p class="text-xs text-gray-400 mt-2">Crie ou selecione um álbum primeiro.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isLoggedIn): ?>
            <div class="flex flex-col md:flex-row gap-8">

                <aside class="w-full md:w-64 shrink-0">
                    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">📁 Álbuns Disponíveis</h2>
                    <?php if (empty($albuns)): ?>
                        <p class="text-sm text-gray-400 italic">Nenhum álbum criado.</p>
                    <?php else: ?>
                        <nav class="space-y-1">
                            <?php foreach ($albuns as $album): ?>
                                <a href="?album=<?= urlencode($album['slug']) ?>" class="block px-4 py-2.5 rounded-xl font-medium text-sm transition <?= ($album_atual && $album['id'] === $album_atual['id']) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white hover:bg-gray-100 text-gray-700 border border-gray-100' ?>">
                                    📂 <?= htmlspecialchars($album['nome']) ?>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                    <?php endif; ?>
                </aside>

                <section class="flex-grow">
                    <form method="POST">
                        <input type="hidden" name="salvar_visibilidade" value="1">

                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                                Exibindo Álbum: <span class="text-gray-800 font-semibold"><?= htmlspecialchars($album_atual['nome'] ?? 'Nenhum') ?></span>
                            </h2>
                            <?php if ($isAdmin && !empty($midias)): ?>
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition shadow-sm">
                                    💾 Salvar Visibilidade
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($midias)): ?>
                            <div class="bg-white border border-gray-200 rounded-xl p-8 text-center text-gray-400 text-sm">
                                Nenhum arquivo disponível neste álbum.
                            </div>
                        <?php else: ?>
                            <div class="columns-1 sm:columns-2 lg:columns-3 gap-4 space-y-4">
                                <?php foreach ($midias as $midia): ?>
                                    <div class="break-inside-avoid bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm flex flex-col justify-between group">
                                        <div class="relative bg-black flex items-center justify-center w-full">
                                            <?php if ($midia['tipo'] === 'image'): ?>
                                                <img src="<?= htmlspecialchars($midia['caminho']) ?>" alt="" class="w-full h-auto object-cover block">
                                            <?php else: ?>
                                                <video src="<?= htmlspecialchars($midia['caminho']) ?>" controls class="w-full h-auto block"></video>
                                            <?php endif; ?>

                                            <?php if ($isAdmin): ?>
                                                <span class="absolute top-2 left-2 text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-full <?= $midia['is_public'] ? 'bg-green-500 text-white' : 'bg-gray-700 text-gray-300' ?>">
                                                    <?= $midia['is_public'] ? 'Público' : 'Privado' ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="p-3 flex justify-between items-center bg-gray-50 border-t border-gray-100 gap-2">
                                            <span class="text-xs text-gray-500 truncate max-w-[120px]"><?= htmlspecialchars($midia['nome_original']) ?></span>

                                            <?php if ($isAdmin): ?>
                                                <div class="flex items-center gap-3">
                                                    <label class="flex items-center gap-1 cursor-pointer text-xs font-medium text-gray-600 hover:text-gray-900">
                                                        <input type="checkbox" name="publicos[]" value="<?= $midia['id'] ?>" <?= $midia['is_public'] ? 'checked' : '' ?> class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                        Público
                                                    </label>
                                                    <a href="?album=<?= urlencode($album_atual['slug']) ?>&delete=<?= $midia['id'] ?>"
                                                        onclick="return confirm('Deseja realmente excluir este arquivo?');"
                                                        class="text-xs text-red-600 hover:text-red-800 font-medium">Excluir</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </form>
                </section>

            </div>
        <?php endif; ?>

    </main>

</body>

</html>