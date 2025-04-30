<x-app-layout>
    <x-slot name="header">
        {{ __('Meus Investimentos') }}
    </x-slot>

    <div class="space-y-6">
        {{-- Portfolio Summary Cards --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <x-card class="bg-gray-850">
                <div class="p-5">
                    <p class="text-sm font-medium text-gray-400">Valor Total do Portfólio</p>
                    <p class="mt-1 text-3xl font-semibold text-white">$ {{ number_format($portfolioTotalValue ?? 0, 2) }} <span class="text-base font-normal text-gray-500">USD</span></p>
                    {{-- Or show in user's preferred currency --}}
                </div>
            </x-card>
             <x-card class="bg-gray-850">
                <div class="p-5">
                    <p class="text-sm font-medium text-gray-400">Retorno Total (P/L)</p>
                     @php $totalPl = $portfolioTotalPl ?? 0; @endphp
                     <p class="mt-1 text-3xl font-semibold {{ $totalPl >= 0 ? 'text-green-400' : 'text-red-400' }}">
                         {{ $totalPl >= 0 ? '+' : '' }}$ {{ number_format(abs($totalPl), 2) }}
                         <span class="text-base font-normal">({{ number_format($portfolioTotalPlPercentage ?? 0, 2) }}%)</span>
                     </p>
                </div>
            </x-card>
             <x-card class="bg-gray-850">
                <div class="p-5">
                    <p class="text-sm font-medium text-gray-400">Variação Diária (24h)</p>
                     @php $dailyChange = $portfolioDailyChange ?? 0; @endphp
                     <p class="mt-1 text-3xl font-semibold {{ $dailyChange >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $dailyChange >= 0 ? '+' : '' }}$ {{ number_format(abs($portfolioDailyChangeValue ?? 0), 2) }}
                        <span class="text-base font-normal">({{ number_format($dailyChange, 2) }}%)</span>
                     </p>
                </div>
            </x-card>
        </div>

        {{-- Portfolio Chart & Holdings --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <x-card>
                    <div class="p-4 border-b border-gray-700">
                        <h3 class="font-semibold text-gray-100">Desempenho do Portfólio</h3>
                        {{-- Add controls for time range (1D, 1W, 1M, 1Y, ALL) using Alpine.js --}}
                    </div>
                    <div class="p-4">
                        {{-- Placeholder for Chart.js or ApexCharts --}}
                        <div class="h-64 bg-gray-700 rounded flex items-center justify-center text-gray-500">
                             Gráfico do Portfólio (Implementar com Chart.js/ApexCharts)
                        </div>
                    </div>
                </x-card>
            </div>
            <div class="lg:col-span-2">
                 <x-card>
                     <div class="p-4 border-b border-gray-700">
                        <h3 class="font-semibold text-gray-100">Alocação por Ativo</h3>
                    </div>
                    <div class="p-4">
                        {{-- Placeholder for Allocation Doughnut Chart --}}
                         <div class="h-64 bg-gray-700 rounded flex items-center justify-center text-gray-500">
                             Gráfico de Alocação (Implementar com Chart.js/ApexCharts)
                         </div>
                         {{-- List top holdings below chart if needed --}}
                    </div>
                </x-card>
            </div>
        </div>


        {{-- Holdings Table --}}
         <x-card>
             <div class="flex items-center justify-between p-4 border-b border-gray-700">
                 <h3 class="font-semibold text-gray-100">Meus Ativos</h3>
                 <x-button.primary href="{{ route('investments.discover') }}"> {{-- Assume rota para descobrir/comprar ativos --}}
                     <x-heroicon-o-search class="w-4 h-4 mr-1"/> Descobrir Ativos
                 </x-button.primary>
             </div>
             <x-table :headers="['Ativo', 'Quantidade', 'Preço Médio', 'Preço Atual', 'Valor Atual', 'P/L Dia', 'P/L Total', 'Ações']">
                 @forelse ($holdings as $holding)
                    <tr class="hover:bg-gray-750">
                        <x-table.td>
                            <div class="flex items-center space-x-3">
                                @if ($holding->asset->logo_url)
                                    <img src="{{ $holding->asset->logo_url }}" alt="{{ $holding->asset->name }} Logo" class="w-8 h-8 rounded-full object-contain bg-white p-0.5">
                                @else
                                    <div class="flex items-center justify-center w-8 h-8 bg-gray-600 rounded-full text-xs text-gray-300 font-semibold">
                                        {{ substr($holding->asset->symbol, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-white">{{ $holding->asset->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $holding->asset->symbol }}</p>
                                </div>
                            </div>
                        </x-table.td>
                        <x-table.td>{{ number_format($holding->quantity, 4) }}</x-table.td>
                        <x-table.td>${{ number_format($holding->average_price, 2) }}</x-table.td>
                        <x-table.td>${{ number_format($holding->asset->current_price, 2) }}</x-table.td>
                        <x-table.td>${{ number_format($holding->current_value, 2) }}</x-table.td>
                         <x-table.td>
                             @php $dailyPl = $holding->daily_pl ?? 0; @endphp
                             <span class="{{ $dailyPl >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                 {{ $dailyPl >= 0 ? '+' : '' }}{{ number_format($dailyPl, 2) }}%
                             </span>
                         </x-table.td>
                         <x-table.td>
                              @php $totalPl = $holding->total_pl ?? 0; @endphp
                              <span class="{{ $totalPl >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                  {{ $totalPl >= 0 ? '+' : '' }}${{ number_format(abs($holding->total_pl_value ?? 0), 2) }}
                                  ({{ number_format($totalPl, 2) }}%)
                              </span>
                         </x-table.td>
                         <x-table.td>
                             <div class="flex space-x-1">
                                 {{-- Botões para Comprar Mais / Vender (abriria modal/página de trade) --}}
                                 <x-button.icon-link href="{{ route('investments.trade', ['asset' => $holding->asset->symbol, 'action' => 'buy']) }}" title="Comprar Mais">
                                     <x-heroicon-o-plus class="w-4 h-4 text-green-400"/>
                                 </x-button.icon-link>
                                 <x-button.icon-link href="{{ route('investments.trade', ['asset' => $holding->asset->symbol, 'action' => 'sell']) }}" title="Vender">
                                      <x-heroicon-o-minus class="w-4 h-4 text-red-400"/>
                                 </x-button.icon-link>
                                  <x-button.icon-link href="{{ route('investments.assetDetails', $holding->asset->symbol) }}" title="Ver Detalhes">
                                      <x-heroicon-o-information-circle class="w-4 h-4 text-gray-400"/>
                                  </x-button.icon-link>
                             </div>
                         </x-table.td>
                    </tr>
                 @empty
                     <tr>
                        <x-table.td colspan="8" class="text-center">Você ainda não possui nenhum investimento.</x-table.td>
                     </tr>
                 @endforelse
             </x-table>
        </x-card>

        {{-- Histórico de Transações de Investimento --}}
        <x-card>
             <div class="flex items-center justify-between p-4 border-b border-gray-700">
                 <h3 class="font-semibold text-gray-100">Histórico de Ordens</h3>
                 {{-- Filtros (opcional) --}}
             </div>
             <x-table :headers="['Data', 'Ativo', 'Tipo', 'Quantidade', 'Preço Unit.', 'Valor Total', 'Status']">
                  @forelse ($investmentTransactions as $tx)
                     <tr class="hover:bg-gray-750">
                         <x-table.td>{{ $tx->created_at->format('d/m/Y H:i') }}</x-table.td>
                         <x-table.td>
                             <div class="flex items-center space-x-2">
                                 @if ($tx->asset->logo_url) <img src="{{ $tx->asset->logo_url }}" class="w-6 h-6 rounded-full object-contain bg-white p-0.5"> @endif
                                 <span>{{ $tx->asset->symbol }}</span>
                             </div>
                         </x-table.td>
                         <x-table.td>{{ $tx->type == 'buy' ? 'Compra' : 'Venda' }}</x-table.td>
                         <x-table.td>{{ number_format($tx->quantity, 4) }}</x-table.td>
                         <x-table.td>${{ number_format($tx->price_per_unit, 2) }}</x-table.td>
                         <x-table.td>${{ number_format($tx->total_value, 2) }}</x-table.td>
                         <x-table.td>
                             <x-badge :color="$tx->status == 'completed' ? 'green' : ($tx->status == 'pending' ? 'yellow' : 'red')">
                                 {{ ucfirst($tx->status) }}
                             </x-badge>
                         </x-table.td>
                     </tr>
                  @empty
                      <tr>
                         <x-table.td colspan="7" class="text-center">Nenhuma transação de investimento encontrada.</x-table.td>
                      </tr>
                  @endforelse
             </x-table>
              {{-- Paginação --}}
             <div class="p-4 bg-gray-800 border-t border-gray-700">
                 {{ $investmentTransactions->links() }}
             </div>
        </x-card>

        {{-- Sugestões de Investimento (Placeholder) --}}
        <x-card>
            <div class="p-4 border-b border-gray-700">
                <h3 class="font-semibold text-gray-100">Sugestões para Você</h3>
                <p class="text-sm text-gray-400">Com base no seu perfil de investidor.</p>
            </div>
             <div class="p-6">
                 {{-- Conteúdo das sugestões (provavelmente vindo do backend) --}}
                 <p class="text-gray-500">Funcionalidade de sugestões ainda não implementada.</p>
                 {{-- Exemplo: Lista de ativos sugeridos com botão "Saber Mais" --}}
             </div>
        </x-card>

    </div>

    {{-- Scripts para Gráficos (Exemplo com ApexCharts) --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            // Exemplo de inicialização de gráfico (adaptar com dados reais)
            document.addEventListener('DOMContentLoaded', function() {
                // Gráfico de Desempenho
                var performanceOptions = {
                  series: [{
                      name: "Portfólio",
                      // Dados de exemplo: [{x: timestamp, y: value}, ...]
                      data: [ {x: 1672531200000, y: 10000}, {x: 1675209600000, y: 10500}, {x: 1677628800000, y: 10300}, {x: 1680307200000, y: 11000} ]
                  }],
                  chart: { type: 'area', height: 250, toolbar: { show: false }, zoom: { enabled: false } },
                  dataLabels: { enabled: false },
                  stroke: { curve: 'smooth', width: 2, colors: ['#FBBF24'] }, // Amber color
                  xaxis: { type: 'datetime', labels: { style: { colors: '#9CA3AF' } }, axisBorder: { color: '#4B5563' }, axisTicks: { color: '#4B5563' } },
                  yaxis: { labels: { style: { colors: '#9CA3AF' }, formatter: (value) => { return '$' + value.toFixed(0); } } },
                  grid: { borderColor: '#374151' }, // gray-700
                  tooltip: { theme: 'dark', x: { format: 'dd MMM yyyy' } },
                  fill: { type: 'gradient', gradient: { shade: 'dark', type: 'vertical', shadeIntensity: 0.5, opacityFrom: 0.7, opacityTo: 0.1, stops: [0, 100], colorStops: [] } },
                  colors: ["#FBBF24"] // Amber color
                };
                var performanceChart = new ApexCharts(document.querySelector("#portfolioPerformanceChart"), performanceOptions); // Assumindo um div com id="portfolioPerformanceChart"
                // performanceChart.render(); // Descomentar quando o div existir

                // Gráfico de Alocação
                var allocationOptions = {
                  series: [44, 55, 13, 33], // Valores percentuais de exemplo
                  labels: ['Ações EUA', 'ETFs Globais', 'Renda Fixa BR', 'GoldStay'], // Labels de exemplo
                  chart: { type: 'donut', height: 270 },
                  dataLabels: { enabled: false },
                  plotOptions: { pie: { donut: { size: '75%' } } },
                  legend: { position: 'bottom', markers: { radius: 12 }, itemMargin: { horizontal: 10 }, labels: { colors: '#D1D5DB' } }, // gray-300
                  tooltip: { theme: 'dark', y: { formatter: function (val) { return val + "%" } } },
                  colors: ['#3B82F6', '#10B981', '#8B5CF6', '#FBBF24'], // Blue, Green, Purple, Amber
                  stroke: { colors:['#1F2937'] } // bg-gray-800
                };
                var allocationChart = new ApexCharts(document.querySelector("#portfolioAllocationChart"), allocationOptions); // Assumindo um div com id="portfolioAllocationChart"
                // allocationChart.render(); // Descomentar quando o div existir

                 // Adicionar lógica para buscar dados reais e atualizar gráficos via Alpine.js/fetch
            });
        </script>
    @endpush

</x-app-layout>