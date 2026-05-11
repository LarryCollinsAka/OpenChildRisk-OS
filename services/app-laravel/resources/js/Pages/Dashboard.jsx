import AppLayout from '../Layouts/AppLayout'
import RiskMap from '../Components/RiskMap'
import { TrendingUp, AlertTriangle, MapPin, Activity, Clock, CheckCircle, AlertCircle } from 'lucide-react'

export default function Dashboard() {
    // OPERATIONAL METRICS (not platform metrics)
    const riskOverview = [
        { 
            name: 'High-Risk Districts', 
            value: '4', 
            subtext: 'Requiring immediate attention',
            trend: '+2 from last week',
            icon: AlertTriangle,
            color: 'red'
        },
        { 
            name: 'Active Alerts', 
            value: '3', 
            subtext: 'Cholera watch, flood warning',
            trend: 'Last 24 hours',
            icon: Activity,
            color: 'orange'
        },
        { 
            name: 'Priority Interventions', 
            value: '7', 
            subtext: 'Pending deployment',
            trend: '2 deployed today',
            icon: CheckCircle,
            color: 'blue'
        },
        { 
            name: 'Vulnerable Children', 
            value: '42.3K', 
            subtext: 'In target districts',
            trend: 'Under-5 + displaced',
            icon: TrendingUp,
            color: 'purple'
        },
    ]

    const activeAlerts = [
        { 
            district: 'Mora', 
            hazard: 'Cholera Watch', 
            severity: 'High', 
            affected: '12,000 under-5',
            reason: 'Poor sanitation (38%) + recent flooding',
            time: '2 hours ago',
            color: 'bg-red-100 text-red-800 border-red-200'
        },
        { 
            district: 'Makary', 
            hazard: 'Flood Warning', 
            severity: 'Medium', 
            affected: '8,500 children',
            reason: 'Heavy rainfall (120mm in 48h)',
            time: '5 hours ago',
            color: 'bg-orange-100 text-orange-800 border-orange-200'
        },
        { 
            district: 'Kousseri', 
            hazard: 'Malaria Spike', 
            severity: 'Medium', 
            affected: '6,200 under-5',
            reason: 'Low vaccination coverage (54%)',
            time: '1 day ago',
            color: 'bg-yellow-100 text-yellow-800 border-yellow-200'
        },
    ]

    const recentChanges = [
        { 
            change: 'Mora district elevated to HIGH risk', 
            indicator: 'Sanitation dropped to 38%',
            time: '2 hours ago', 
            icon: AlertCircle, 
            color: 'text-red-600' 
        },
        { 
            change: 'CHIRPS rainfall data updated', 
            indicator: '120mm in 48h (3x normal)',
            time: '3 hours ago', 
            icon: Clock, 
            color: 'text-blue-600' 
        },
        { 
            change: 'Yagoua vaccination campaign deployed', 
            indicator: '2,400 children reached',
            time: '4 hours ago', 
            icon: CheckCircle, 
            color: 'text-green-600' 
        },
    ]

    const districtRiskSnapshot = [
        { district: 'Mora', risk: 9.2, status: 'Critical', population: '12,000', factors: 'Poor WASH + Flood' },
        { district: 'Makary', risk: 7.8, status: 'High', population: '8,500', factors: 'Heavy rainfall' },
        { district: 'Kousseri', risk: 6.4, status: 'Medium', population: '6,200', factors: 'Low vaccination' },
        { district: 'Yagoua', risk: 4.2, status: 'Low', population: '5,800', factors: 'Stable conditions' },
    ]

    const dataFreshness = [
        { source: 'CHIRPS Rainfall', updated: '2 hours ago', status: 'current', color: 'bg-green-500' },
        { source: 'DHIS2 Health Data', updated: '6 hours ago', status: 'current', color: 'bg-green-500' },
        { source: 'Population Stats', updated: '2 days ago', status: 'acceptable', color: 'bg-yellow-500' },
        { source: 'ACLED Conflict Data', updated: '1 week ago', status: 'stale', color: 'bg-orange-500' },
    ]

    // District data for map
    const mapDistricts = [
        { name: 'Mora', lat: 11.0455, lng: 14.1392, risk: 9.2, status: 'Critical', population: '12,000', factors: 'Poor WASH + Flood' },
        { name: 'Makary', lat: 12.5739, lng: 14.4581, risk: 7.8, status: 'High', population: '8,500', factors: 'Heavy rainfall' },
        { name: 'Kousseri', lat: 12.0778, lng: 15.0308, risk: 6.4, status: 'Medium', population: '6,200', factors: 'Low vaccination' },
        { name: 'Yagoua', lat: 10.3414, lng: 15.2372, risk: 4.2, status: 'Low', population: '5,800', factors: 'Stable conditions' },
    ]

    const mapFacilities = [
        { name: 'Mora District Hospital', lat: 11.0455, lng: 14.1392, type: 'Hospital', status: 'Operational' },
        { name: 'Makary Health Center', lat: 12.5739, lng: 14.4581, type: 'Health Center', status: 'Operational' },
        { name: 'Kousseri Clinic', lat: 12.0778, lng: 15.0308, type: 'Clinic', status: 'Limited capacity' },
    ]

    return (
        <AppLayout title="Risk Intelligence Dashboard">
            {/* Critical Alerts Banner */}
            <div className="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 mb-6">
                <div className="flex items-center">
                    <AlertTriangle className="w-6 h-6 text-red-600 mr-3" />
                    <div className="flex-1">
                        <p className="text-sm font-semibold text-red-800">
                            4 districts require immediate attention
                        </p>
                        <p className="text-sm text-red-700 mt-1">
                            Mora (Critical), Makary (High), Kousseri (Medium) — View details below
                        </p>
                    </div>
                    <button className="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">
                        View All Alerts
                    </button>
                </div>
            </div>

            {/* Operational Metrics */}
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                {riskOverview.map((stat) => {
                    const Icon = stat.icon
                    const colorClasses = {
                        red: 'bg-red-50 text-red-600 border-red-200',
                        orange: 'bg-orange-50 text-orange-600 border-orange-200',
                        blue: 'bg-blue-50 text-blue-600 border-blue-200',
                        purple: 'bg-purple-50 text-purple-600 border-purple-200',
                    }
                    return (
                        <div key={stat.name} className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className={`w-12 h-12 rounded-lg flex items-center justify-center border-2 ${colorClasses[stat.color]}`}>
                                        <Icon className="w-6 h-6" />
                                    </div>
                                </div>
                                <div className="ml-4 flex-1">
                                    <p className="text-sm font-medium text-gray-500">{stat.name}</p>
                                    <div className="flex items-baseline mt-1">
                                        <p className="text-3xl font-bold text-gray-900">{stat.value}</p>
                                    </div>
                                    <p className="text-xs text-gray-500 mt-1">{stat.subtext}</p>
                                    <p className="text-xs text-gray-400 mt-1">{stat.trend}</p>
                                </div>
                            </div>
                        </div>
                    )
                })}
            </div>

            {/* MAP + ALERTS SIDE BY SIDE */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {/* Risk Map */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 h-[500px]">
                    <div className="px-6 py-4 border-b border-gray-200">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">Far North Region — Risk Map</h3>
                            <div className="flex items-center space-x-4 text-xs">
                                <div className="flex items-center">
                                    <div className="w-3 h-3 rounded-full bg-red-600 mr-1"></div>
                                    <span>Critical</span>
                                </div>
                                <div className="flex items-center">
                                    <div className="w-3 h-3 rounded-full bg-orange-600 mr-1"></div>
                                    <span>High</span>
                                </div>
                                <div className="flex items-center">
                                    <div className="w-3 h-3 rounded-full bg-yellow-500 mr-1"></div>
                                    <span>Medium</span>
                                </div>
                                <div className="flex items-center">
                                    <div className="w-3 h-3 rounded-full bg-green-600 mr-1"></div>
                                    <span>Low</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="h-[calc(500px-60px)]">
                        <RiskMap districts={mapDistricts} facilities={mapFacilities} />
                    </div>
                </div>

                {/* Active Alerts */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div className="px-6 py-4 border-b border-gray-200">
                        <h3 className="text-lg font-semibold text-gray-900">Active Alerts</h3>
                    </div>
                    <div className="p-6 space-y-4">
                        {activeAlerts.map((alert, idx) => (
                            <div key={idx} className={`border-2 rounded-lg p-4 ${alert.color}`}>
                                <div className="flex items-start justify-between mb-2">
                                    <div>
                                        <h4 className="font-semibold">{alert.district}</h4>
                                        <p className="text-sm font-medium">{alert.hazard}</p>
                                    </div>
                                    <span className="px-2 py-1 text-xs font-bold rounded">
                                        {alert.severity}
                                    </span>
                                </div>
                                <p className="text-sm mb-1">
                                    <span className="font-medium">Affected:</span> {alert.affected}
                                </p>
                                <p className="text-sm mb-2">
                                    <span className="font-medium">Why:</span> {alert.reason}
                                </p>
                                <p className="text-xs opacity-75">{alert.time}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* What Changed Today */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div className="px-6 py-4 border-b border-gray-200">
                    <h3 className="text-lg font-semibold text-gray-900">What Changed Today</h3>
                </div>
                <div className="p-6">
                    <div className="flow-root">
                        <ul className="-mb-8">
                            {recentChanges.map((item, idx) => {
                                const Icon = item.icon
                                return (
                                    <li key={idx}>
                                        <div className="relative pb-8">
                                            {idx !== recentChanges.length - 1 && (
                                                <span className="absolute left-5 top-5 -ml-px h-full w-0.5 bg-gray-200" />
                                            )}
                                            <div className="relative flex items-start space-x-3">
                                                <div className="relative">
                                                    <div className="h-10 w-10 rounded-full bg-gray-50 flex items-center justify-center ring-8 ring-white">
                                                        <Icon className={`h-5 w-5 ${item.color}`} />
                                                    </div>
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <p className="text-sm font-medium text-gray-900">
                                                        {item.change}
                                                    </p>
                                                    <p className="text-sm text-gray-600 mt-0.5">
                                                        {item.indicator}
                                                    </p>
                                                    <p className="text-xs text-gray-500 mt-1">
                                                        {item.time}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                )
                            })}
                        </ul>
                    </div>
                </div>
            </div>

            {/* District Risk Snapshot */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div className="px-6 py-4 border-b border-gray-200">
                    <h3 className="text-lg font-semibold text-gray-900">District Risk Snapshot — Far North Region</h3>
                </div>
                <div className="overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">District</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Score</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vulnerable Children</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key Factors</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {districtRiskSnapshot.map((district) => {
                                const statusColors = {
                                    'Critical': 'bg-red-100 text-red-800',
                                    'High': 'bg-orange-100 text-orange-800',
                                    'Medium': 'bg-yellow-100 text-yellow-800',
                                    'Low': 'bg-green-100 text-green-800',
                                }
                                return (
                                    <tr key={district.district} className="hover:bg-gray-50">
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {district.district}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            {district.risk}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className={`px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${statusColors[district.status]}`}>
                                                {district.status}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {district.population}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-600">
                                            {district.factors}
                                        </td>
                                    </tr>
                                )
                            })}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Data Freshness */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200">
                <div className="px-6 py-4 border-b border-gray-200">
                    <h3 className="text-lg font-semibold text-gray-900">Data Freshness</h3>
                </div>
                <div className="p-6">
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                        {dataFreshness.map((source) => (
                            <div key={source.source} className="flex items-center justify-between">
                                <div className="flex items-center flex-1">
                                    <div className={`w-2 h-2 rounded-full ${source.color} mr-3`} />
                                    <div>
                                        <p className="text-sm font-medium text-gray-900">{source.source}</p>
                                        <p className="text-xs text-gray-500">{source.updated}</p>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    )
}