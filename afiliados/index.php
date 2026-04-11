<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Afiliados - Professor Eugênio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }

        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-link {
            transition: all 0.3s ease;
        }

        .sidebar-link:hover {
            background: rgba(99, 102, 241, 0.1);
            transform: translateX(5px);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

    <!-- Estrutura do Sistema de Afiliados -->

    <!-- COMPONENTE: nav.php -->
    <nav id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-slate-900 text-white transform -translate-x-full md:translate-x-0 transition-transform duration-300 z-50">
        <div class="p-6 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg">Prof. Eugênio</h1>
                    <p class="text-xs text-slate-400">Sistema de Afiliados</p>
                </div>
            </div>
        </div>

        <div class="p-4 space-y-2">
            <a href="index.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-600 text-white">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                <span>Dashboard</span>
            </a>

            <a href="produtos.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white">
                <i data-lucide="package" class="w-5 h-5"></i>
                <span>Meus Produtos</span>
            </a>

            <a href="extrato.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white">
                <i data-lucide="file-text" class="w-5 h-5"></i>
                <span>Extrato</span>
            </a>

            <a href="pagamentosrecebidos.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white">
                <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                <span>Pagamentos</span>
            </a>

            <a href="solicitacoes.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white">
                <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                <span>Solicitações</span>
                <span class="ml-auto bg-red-500 text-xs px-2 py-1 rounded-full">3</span>
            </a>

            <a href="meusdados.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:text-white">
                <i data-lucide="user" class="w-5 h-5"></i>
                <span>Meus Dados</span>
            </a>
        </div>

        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-800">
            <div class="flex items-center gap-3 px-4 py-3">
                <img src="https://ui-avatars.com/api/?name=Afiliado+Silva&background=6366f1&color=fff" class="w-10 h-10 rounded-full">
                <div>
                    <p class="text-sm font-medium">Carlos Silva</p>
                    <p class="text-xs text-slate-400">carlos@email.com</p>
                </div>
            </div>
            <a href="#" class="flex items-center gap-3 px-4 py-2 text-red-400 hover:text-red-300 mt-2">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                <span>Sair</span>
            </a>
        </div>
    </nav>

    <!-- Overlay para mobile -->
    <div id="overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden" onclick="toggleSidebar()"></div>

    <!-- Conteúdo Principal -->
    <main class="md:ml-64 min-h-screen">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="flex items-center justify-between px-6 py-4">
                <button onclick="toggleSidebar()" class="md:hidden p-2 hover:bg-gray-100 rounded-lg">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>

                <div class="flex items-center gap-4 ml-auto">
                    <button class="relative p-2 hover:bg-gray-100 rounded-lg">
                        <i data-lucide="bell" class="w-5 h-5 text-gray-600"></i>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    <div class="hidden sm:flex items-center gap-2 px-4 py-2 bg-indigo-50 rounded-lg">
                        <i data-lucide="link" class="w-4 h-4 text-indigo-600"></i>
                        <span class="text-sm text-indigo-700 font-medium">eugenio.com/af/carlos123</span>
                        <button onclick="copyLink()" class="ml-2 text-indigo-600 hover:text-indigo-800">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- PÁGINA: index.php (Dashboard) -->
        <div id="page-index" class="p-6 animate-fade-in">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
                <p class="text-gray-600">Bem-vindo de volta, Carlos! Aqui está o resumo dos seus ganhos.</p>
            </div>

            <!-- Cards de Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl p-6 shadow-sm card-hover border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-green-100 rounded-xl">
                            <i data-lucide="trending-up" class="w-6 h-6 text-green-600"></i>
                        </div>
                        <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">+12%</span>
                    </div>
                    <p class="text-gray-600 text-sm">Ganhos Totais</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">R$ 12.450,00</h3>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm card-hover border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-100 rounded-xl">
                            <i data-lucide="shopping-cart" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">8 novos</span>
                    </div>
                    <p class="text-gray-600 text-sm">Vendas este Mês</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">47</h3>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm card-hover border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-purple-100 rounded-xl">
                            <i data-lucide="users" class="w-6 h-6 text-purple-600"></i>
                        </div>
                        <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded-full">+5%</span>
                    </div>
                    <p class="text-gray-600 text-sm">Visitantes</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">1.234</h3>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm card-hover border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-orange-100 rounded-xl">
                            <i data-lucide="wallet" class="w-6 h-6 text-orange-600"></i>
                        </div>
                        <span class="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-1 rounded-full">Pendente</span>
                    </div>
                    <p class="text-gray-600 text-sm">Saldo Disponível</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">R$ 3.200,00</h3>
                </div>
            </div>

            <!-- Gráfico e Tabela -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-6">Desempenho dos Últimos 6 Meses</h3>
                    <div class="h-64 flex items-end justify-between gap-2">
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-indigo-100 rounded-t-lg relative group" style="height: 40%">
                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition">R$ 2.400</div>
                            </div>
                            <span class="text-xs text-gray-600">Nov</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-indigo-200 rounded-t-lg relative group" style="height: 55%">
                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition">R$ 3.100</div>
                            </div>
                            <span class="text-xs text-gray-600">Dez</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-indigo-300 rounded-t-lg relative group" style="height: 45%">
                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition">R$ 2.800</div>
                            </div>
                            <span class="text-xs text-gray-600">Jan</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-indigo-400 rounded-t-lg relative group" style="height: 70%">
                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition">R$ 4.200</div>
                            </div>
                            <span class="text-xs text-gray-600">Fev</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-indigo-500 rounded-t-lg relative group" style="height: 60%">
                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition">R$ 3.600</div>
                            </div>
                            <span class="text-xs text-gray-600">Mar</span>
                        </div>
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-indigo-600 rounded-t-lg relative group" style="height: 85%">
                                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition">R$ 5.100</div>
                            </div>
                            <span class="text-xs text-gray-600">Abr</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Últimas Vendas</h3>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-xl transition">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i data-lucide="check" class="w-5 h-5 text-green-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Curso de Matemática</p>
                                <p class="text-xs text-gray-500">Hoje, 14:30</p>
                            </div>
                            <span class="font-bold text-green-600">+R$ 120</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-xl transition">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i data-lucide="check" class="w-5 h-5 text-green-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Pacote Completo</p>
                                <p class="text-xs text-gray-500">Ontem, 18:45</p>
                            </div>
                            <span class="font-bold text-green-600">+R$ 450</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-xl transition">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i data-lucide="clock" class="w-5 h-5 text-yellow-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">Física Básica</p>
                                <p class="text-xs text-gray-500">Pendente</p>
                            </div>
                            <span class="font-bold text-yellow-600">R$ 80</span>
                        </div>
                    </div>
                    <button class="w-full mt-4 py-2 text-indigo-600 font-medium hover:bg-indigo-50 rounded-lg transition">Ver todas</button>
                </div>
            </div>
        </div>

        <!-- PÁGINA: produtos.php -->
        <div id="page-produtos" class="p-6 hidden animate-fade-in">
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Meus Produtos</h2>
                    <p class="text-gray-600">Gerencie os cursos que você promove</p>
                </div>
                <button class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition flex items-center gap-2">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    Solicitar Novo Produto
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <!-- Card Produto 1 -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm card-hover border border-gray-100">
                    <div class="h-48 bg-gradient-to-br from-indigo-500 to-purple-600 relative">
                        <div class="absolute top-4 right-4 bg-white/20 backdrop-blur px-3 py-1 rounded-full text-white text-sm font-medium">
                            30% comissão
                        </div>
                        <div class="absolute bottom-4 left-4 text-white">
                            <h3 class="text-xl font-bold">Matemática Avançada</h3>
                            <p class="text-white/80 text-sm">Professor Eugênio</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-2xl font-bold text-gray-800">R$ 397,00</span>
                            <span class="text-green-600 font-medium">R$ 119,10/ venda</span>
                        </div>
                        <div class="space-y-2 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Vendas: 24</span>
                                <span class="text-gray-600">Conversão: 3.2%</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button class="flex-1 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center justify-center gap-2">
                                <i data-lucide="link" class="w-4 h-4"></i>
                                Copiar Link
                            </button>
                            <button class="flex-1 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                                <i data-lucide="share-2" class="w-4 h-4"></i>
                                Compartilhar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card Produto 2 -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm card-hover border border-gray-100">
                    <div class="h-48 bg-gradient-to-br from-blue-500 to-cyan-600 relative">
                        <div class="absolute top-4 right-4 bg-white/20 backdrop-blur px-3 py-1 rounded-full text-white text-sm font-medium">
                            25% comissão
                        </div>
                        <div class="absolute bottom-4 left-4 text-white">
                            <h3 class="text-xl font-bold">Física para ENEM</h3>
                            <p class="text-white/80 text-sm">Professor Eugênio</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-2xl font-bold text-gray-800">R$ 297,00</span>
                            <span class="text-green-600 font-medium">R$ 74,25/ venda</span>
                        </div>
                        <div class="space-y-2 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Vendas: 18</span>
                                <span class="text-gray-600">Conversão: 2.8%</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button class="flex-1 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center justify-center gap-2">
                                <i data-lucide="link" class="w-4 h-4"></i>
                                Copiar Link
                            </button>
                            <button class="flex-1 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                                <i data-lucide="share-2" class="w-4 h-4"></i>
                                Compartilhar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card Produto 3 -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm card-hover border border-gray-100">
                    <div class="h-48 bg-gradient-to-br from-orange-500 to-red-600 relative">
                        <div class="absolute top-4 right-4 bg-white/20 backdrop-blur px-3 py-1 rounded-full text-white text-sm font-medium">
                            35% comissão
                        </div>
                        <div class="absolute bottom-4 left-4 text-white">
                            <h3 class="text-xl font-bold">Pacote Completo</h3>
                            <p class="text-white/80 text-sm">Professor Eugênio</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-2xl font-bold text-gray-800">R$ 997,00</span>
                            <span class="text-green-600 font-medium">R$ 348,95/ venda</span>
                        </div>
                        <div class="space-y-2 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Vendas: 5</span>
                                <span class="text-gray-600">Conversão: 1.5%</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button class="flex-1 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center justify-center gap-2">
                                <i data-lucide="link" class="w-4 h-4"></i>
                                Copiar Link
                            </button>
                            <button class="flex-1 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                                <i data-lucide="share-2" class="w-4 h-4"></i>
                                Compartilhar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PÁGINA: extrato.php -->
        <div id="page-extrato" class="p-6 hidden animate-fade-in">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Extrato de Comissões</h2>
                <p class="text-gray-600">Histórico completo das suas comissões</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row gap-4 justify-between items-center">
                    <div class="flex gap-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium">Todas</button>
                        <button class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium">Aprovadas</button>
                        <button class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium">Pendentes</button>
                        <button class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium">Canceladas</button>
                    </div>
                    <div class="flex gap-2">
                        <input type="date" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">
                            <i data-lucide="download" class="w-4 h-4 inline mr-2"></i>
                            Exportar
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comissão</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">06/04/2026</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Matemática Avançada</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">João Silva</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">R$ 397,00</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">R$ 119,10</td>
                                <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Aprovado</span></td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">05/04/2026</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Física para ENEM</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Maria Santos</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">R$ 297,00</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">R$ 74,25</td>
                                <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Aprovado</span></td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">04/04/2026</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Pacote Completo</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Pedro Costa</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">R$ 997,00</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-yellow-600">R$ 348,95</td>
                                <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pendente</span></td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">03/04/2026</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Matemática Avançada</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Ana Paula</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">R$ 397,00</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">R$ 0,00</td>
                                <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Cancelado</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <p class="text-sm text-gray-600">Mostrando 1-4 de 47 resultados</p>
                    <div class="flex gap-2">
                        <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50" disabled>Anterior</button>
                        <button class="px-3 py-1 bg-indigo-600 text-white rounded-lg text-sm">1</button>
                        <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">2</button>
                        <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">3</button>
                        <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Próximo</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- PÁGINA: pagamentosrecebidos.php -->
        <div id="page-pagamentos" class="p-6 hidden animate-fade-in">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Pagamentos Recebidos</h2>
                <p class="text-gray-600">Histórico de saques e transferências</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white">
                    <p class="text-indigo-200 mb-2">Saldo Disponível</p>
                    <h3 class="text-3xl font-bold mb-4">R$ 3.200,00</h3>
                    <button class="w-full py-3 bg-white text-indigo-600 rounded-xl font-medium hover:bg-indigo-50 transition">
                        Solicitar Saque
                    </button>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <p class="text-gray-600 mb-2">Total Recebido (2026)</p>
                    <h3 class="text-3xl font-bold text-gray-800 mb-2">R$ 8.450,00</h3>
                    <p class="text-sm text-green-600 flex items-center gap-1">
                        <i data-lucide="trending-up" class="w-4 h-4"></i>
                        +23% vs ano anterior
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <p class="text-gray-600 mb-2">Próximo Pagamento</p>
                    <h3 class="text-3xl font-bold text-gray-800 mb-2">R$ 1.250,00</h3>
                    <p class="text-sm text-gray-500">Previsto para 15/04/2026</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800">Histórico de Pagamentos</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i data-lucide="arrow-down-left" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Transferência PIX</p>
                                <p class="text-sm text-gray-500">15/03/2026 • Banco Itaú</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-800">R$ 2.100,00</p>
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">Concluído</span>
                        </div>
                    </div>

                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i data-lucide="arrow-down-left" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Transferência PIX</p>
                                <p class="text-sm text-gray-500">15/02/2026 • Banco Itaú</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-800">R$ 1.850,00</p>
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">Concluído</span>
                        </div>
                    </div>

                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i data-lucide="arrow-down-left" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Transferência PIX</p>
                                <p class="text-sm text-gray-500">15/01/2026 • Banco Itaú</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-800">R$ 3.200,00</p>
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded-full">Concluído</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PÁGINA: solicitacoes.php -->
        <div id="page-solicitacoes" class="p-6 hidden animate-fade-in">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Solicitações</h2>
                <p class="text-gray-600">Gerencie suas solicitações de saque e suporte</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Nova Solicitação -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Nova Solicitação de Saque</h3>
                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor Disponível</label>
                            <input type="text" value="R$ 3.200,00" disabled class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor do Saque *</label>
                            <input type="number" placeholder="0,00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chave PIX *</label>
                            <input type="text" placeholder="CPF, Email ou Telefone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <button type="submit" class="w-full py-3 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition">
                            Solicitar Saque
                        </button>
                    </form>
                </div>

                <!-- Solicitações Pendentes -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Solicitações em Andamento</h3>
                    <div class="space-y-4">
                        <div class="p-4 border border-yellow-200 bg-yellow-50 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-gray-800">Saque #1234</span>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full">Em análise</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-1">Valor: R$ 1.500,00</p>
                            <p class="text-xs text-gray-500">Solicitado em 05/04/2026</p>
                        </div>

                        <div class="p-4 border border-green-200 bg-green-50 rounded-xl">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-gray-800">Saque #1230</span>
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Aprovado</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-1">Valor: R$ 2.100,00</p>
                            <p class="text-xs text-gray-500">Pago em 15/03/2026</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PÁGINA: meusdados.php -->
        <div id="page-dados" class="p-6 hidden animate-fade-in">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Meus Dados</h2>
                <p class="text-gray-600">Gerencie suas informações pessoais e bancárias</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Perfil -->
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <div class="text-center mb-6">
                        <div class="relative inline-block">
                            <img src="https://ui-avatars.com/api/?name=Carlos+Silva&background=6366f1&color=fff&size=128" class="w-24 h-24 rounded-full mx-auto">
                            <button class="absolute bottom-0 right-0 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center hover:bg-indigo-700">
                                <i data-lucide="camera" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mt-4">Carlos Silva</h3>
                        <p class="text-gray-600">Afiliado desde 2024</p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-sm">
                            <i data-lucide="mail" class="w-4 h-4 text-gray-400"></i>
                            <span class="text-gray-600">carlos.silva@email.com</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                            <span class="text-gray-600">(11) 98765-4321</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <i data-lucide="map-pin" class="w-4 h-4 text-gray-400"></i>
                            <span class="text-gray-600">São Paulo, SP</span>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Edição -->
                <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-6">Editar Perfil</h3>
                    <form class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                                <input type="text" value="Carlos Silva" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                <input type="email" value="carlos.silva@email.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone *</label>
                                <input type="tel" value="(11) 98765-4321" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CPF *</label>
                                <input type="text" value="123.456.789-00" disabled class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="font-medium text-gray-800 mb-4">Dados Bancários</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Chave PIX Principal *</label>
                                    <input type="text" value="carlos.silva@email.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Banco</label>
                                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option>Itaú Unibanco</option>
                                        <option>Bradesco</option>
                                        <option>Santander</option>
                                        <option>Banco do Brasil</option>
                                        <option>Caixa Econômica</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition">
                                Salvar Alterações
                            </button>
                            <button type="button" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Inicializar ícones
        lucide.createIcons();

        // Função para alternar sidebar mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Função para copiar link de afiliado
        function copyLink() {
            navigator.clipboard.writeText('eugenio.com/af/carlos123');
            alert('Link copiado para a área de transferência!');
        }

        // Navegação entre páginas (simulação)
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');

                // Esconder todas as páginas
                document.querySelectorAll('[id^="page-"]').forEach(page => {
                    page.classList.add('hidden');
                });

                // Mostrar página correspondente
                const pageMap = {
                    'index.php': 'page-index',
                    'produtos.php': 'page-produtos',
                    'extrato.php': 'page-extrato',
                    'pagamentosrecebidos.php': 'page-pagamentos',
                    'solicitacoes.php': 'page-solicitacoes',
                    'meusdados.php': 'page-dados'
                };

                const pageId = pageMap[href];
                if (pageId) {
                    document.getElementById(pageId).classList.remove('hidden');
                }

                // Atualizar active state
                document.querySelectorAll('.sidebar-link').forEach(l => {
                    l.classList.remove('bg-indigo-600', 'text-white');
                    l.classList.add('text-slate-300');
                });
                this.classList.remove('text-slate-300');
                this.classList.add('bg-indigo-600', 'text-white');

                // Fechar sidebar mobile
                if (window.innerWidth < 768) {
                    toggleSidebar();
                }
            });
        });
    </script>

</body>

</html>