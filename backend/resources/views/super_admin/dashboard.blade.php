@extends('layouts.super_admin')

@section('title', 'Global Dashboard')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Stat Cards (Dark Theme) -->
        <div class="bg-gray-800 rounded-lg shadow-lg p-5 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-gray-700 p-3 rounded-full">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-gray-400 text-sm font-medium">Total Active LPKs</h4>
                    <p class="text-2xl font-bold text-gray-100">{{ $stats['total_tenants'] }}</p>
                    <div class="flex items-center text-xs text-green-400 mt-1">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        <span>+5 this month</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg shadow-lg p-5 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-gray-700 p-3 rounded-full">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-gray-400 text-sm font-medium">Monthly GMV</h4>
                    <p class="text-2xl font-bold text-gray-100">IDR 450M</p>
                    <div class="flex items-center text-xs text-green-400 mt-1">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        <span>+12% vs last month</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg shadow-lg p-5 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-gray-700 p-3 rounded-full">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-gray-400 text-sm font-medium">Active Students</h4>
                    <p class="text-2xl font-bold text-gray-100">12.5k</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg shadow-lg p-5 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-gray-700 p-3 rounded-full">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="text-gray-400 text-sm font-medium">Pending Verifications</h4>
                    <p class="text-2xl font-bold text-gray-100">{{ $stats['pending_verifications'] }}</p>
                    <a href="#" class="text-xs text-yellow-500 hover:text-yellow-400 mt-1 block">Review Queue &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Chart -->
        <div class="bg-gray-800 rounded-lg shadow-lg lg:col-span-2">
            <div class="px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                <h3 class="font-semibold text-gray-100">Revenue Trend</h3>
                <select class="bg-gray-700 text-gray-300 text-sm rounded border-none focus:ring-0">
                    <option>Last 30 Days</option>
                    <option>Last 6 Months</option>
                    <option>Year to Date</option>
                </select>
            </div>
            <div class="p-6">
                <canvas id="revenueChart" height="150"></canvas>
            </div>
        </div>

        <!-- System Health -->
        <div class="bg-gray-800 rounded-lg shadow-lg">
            <div class="px-6 py-4 border-b border-gray-700">
                <h3 class="font-semibold text-gray-100">System Health</h3>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-400">API Response Time</span>
                        <span class="text-green-400 font-bold">124ms</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: 15%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-400">Database Load</span>
                        <span class="text-blue-400 font-bold">42%</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: 42%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-400">Queue Jobs (Horizon)</span>
                        <span class="text-purple-400 font-bold">Running</span>
                    </div>
                    <div class="flex space-x-2 mt-2">
                        <span class="px-2 py-1 text-xs bg-gray-700 rounded text-gray-300">Pending: 0</span>
                        <span class="px-2 py-1 text-xs bg-gray-700 rounded text-gray-300">Failed: 0</span>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-400">Storage (S3)</span>
                        <span class="text-yellow-400 font-bold">145 GB</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-gray-800 rounded-lg shadow-lg">
        <div class="px-6 py-4 border-b border-gray-700">
            <h3 class="font-semibold text-gray-100">Recent LPK Verifications</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-gray-300">
                <thead class="bg-gray-700 text-xs uppercase font-medium text-gray-400">
                    <tr>
                        <th class="px-6 py-3">LPK Name</th>
                        <th class="px-6 py-3">Location</th>
                        <th class="px-6 py-3">Submitted</th>
                        <th class="px-6 py-3">Documents</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <tr>
                        <td class="px-6 py-4 font-medium text-white">LPK Merah Putih</td>
                        <td class="px-6 py-4">Indramayu</td>
                        <td class="px-6 py-4">2 hrs ago</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs bg-green-900 text-green-300 rounded">Complete</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs bg-yellow-900 text-yellow-300 rounded">Pending</span>
                        </td>
                        <td class="px-6 py-4">
                            <button class="text-blue-400 hover:text-blue-300 text-sm">Review</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 font-medium text-white">Techno Skill Center</td>
                        <td class="px-6 py-4">Bandung</td>
                        <td class="px-6 py-4">5 hrs ago</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs bg-red-900 text-red-300 rounded">Missing NIB</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs bg-red-900 text-red-300 rounded">Incomplete</span>
                        </td>
                        <td class="px-6 py-4">
                            <button class="text-blue-400 hover:text-blue-300 text-sm">Review</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'GMV (Millions)',
                        data: [120, 190, 300, 500, 200, 450],
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: { color: '#9CA3AF' }
                        }
                    },
                    scales: {
                        y: {
                            grid: { color: '#374151' },
                            ticks: { color: '#9CA3AF' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#9CA3AF' }
                        }
                    }
                }
            });
        });
    </script>
@endsection