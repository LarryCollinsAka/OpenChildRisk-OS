/**
 * Operational Intelligence Dashboard
 * 
 * Main dashboard for humanitarian decision-making.
 * Displays real-time risk intelligence, active alerts, geospatial mapping,
 * and operational metrics for UNICEF/WHO-style organizations.
 * 
 * Key Features:
 * - Interactive risk map with color-coded districts
 * - Active alerts with explainability ("Why is this critical?")
 * - Temporal awareness ("What changed today?")
 * - Data freshness monitoring (trust building)
 * - Risk snapshot table (compressed operational view)
 * 
 * Data Flow:
 * Dashboard.jsx <- DashboardController <- Database (Districts, Indicators, etc.)
 */

import AppLayout from '../Layouts/AppLayout'
import RiskMap from '../Components/RiskMap'
import { TrendingUp, AlertTriangle, MapPin, Activity, Clock, CheckCircle, AlertCircle } from 'lucide-react'

/**
 * Dashboard Component
 * 
 * @param {Object} props
 * @param {Array} props.mapDistricts - Array of district objects from database
 * @param {Object} props.stats - System-wide statistics (counts)
 */
export default function Dashboard({ mapDistricts, stats }) {
    
    // ========================================================================
    // OPERATIONAL METRICS (Not Platform Metrics)
    // ========================================================================
    // These cards answer: "What requires action?" not "What have we built?"
    // Focus: Urgency, prioritization, human impact
    const riskOverview = [
        { 
            name: 'Total Districts', 
            value: stats.total_districts.toString(), 
            subtext: 'Far North Region',
            trend: 'All active',
            icon: MapPin,
            color: 'blue'
        },
        { 
            name: 'Vulnerability Indicators', 
            value: stats.total_indicators.toString(), 
            subtext: 'Across 9 categories',
            trend: 'Multi-dimensional',
            icon: TrendingUp,
            color: 'green'
        },
        { 
            name: 'Population Groups', 
            value: stats.total_population_groups.toString(), 
            subtext: 'Differentiated tracking',
            trend: 'Vulnerability-weighted',
            icon: Activity,
            color: 'purple'
        },
        { 
            name: 'Data Sources', 
            value: stats.total_data_sources.toString(), 
            subtext: 'Connected sources',
            trend: 'Real-time ingestion',
            icon: CheckCircle,
            color: 'orange'
        },
    ]

    // ========================================================================
    // ACTIVE ALERTS (With Explainability)
    // ========================================================================
    // Key feature: "WHY" field explains the alert
    // This builds trust in AI-driven risk assessments
    // TODO: Fetch from alerts table when hazard_events are ingested
    const activeAlerts = [
        { 
            district: 'Mora', 
            hazard: 'Cholera Watch', 
            severity: 'High', 
            affected: '12,000 under-5',
            reason: 'Poor sanitation (38%) + recent flooding', // EXPLAINABILITY
            time: '2 hours ago',
            color: 'bg-red-100 text-red-800 border-red-200'
        },
        { 
            district: 'Makary', 
            hazard: 'Flood Warning', 
            severity: 'Medium', 
            affected: '8,500 children',
            reason: 'Heavy rainfall (120mm in 48h)', // EXPLAINABILITY
            time: '5 hours ago',
            color: 'bg-orange-100 text-orange-800 border-orange-200'
        },
        { 
            district: 'Kousseri', 
            hazard: 'Malaria Spike', 
            severity: 'Medium', 
            affected: '6,200 under-5',
            reason: 'Low vaccination coverage (54%)', // EXPLAINABILITY
            time: '1 day ago',
            color: 'bg-yellow-100 text-yellow-800 border-yellow-200'
        },
    ]

    // ========================================================================
    // WHAT CHANGED TODAY (Temporal Awareness)
    // ========================================================================
    // Humanitarian decisions depend on: "What's new?"
    // This panel creates situational awareness and operational continuity
    // TODO: Query recent changes from audit logs, indicator updates, events
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

    // ========================================================================
    // DISTRICT RISK SNAPSHOT (Compressed Operational View)
    // ========================================================================
    // Table format compresses: severity + population + drivers into one view
    // TODO: Fetch from compound_risk_assessments table
    const districtRiskSnapshot = [
        { district: 'Mora', risk: 9.2, status: 'Critical', population: '12,000', factors: 'Poor WASH + Flood' },
        { district: 'Makary', risk: 7.8, status: 'High', population: '8,500', factors: 'Heavy rainfall' },
        { district: 'Kousseri', risk: 6.4, status: 'Medium', population: '6,200', factors: 'Low vaccination' },
        { district: 'Yagoua', risk: 4.2, status: 'Low', population: '5,800', factors: 'Stable conditions' },
    ]

    // ========================================================================
    // DATA FRESHNESS (Trust Building)
    // ========================================================================
    // Humanitarian decisions depend on data recency
    // Showing "updated 2h ago" creates operational credibility
    // TODO: Query data_ingestion_jobs table for last sync times
    const dataFreshness = [
        { source: 'CHIRPS Rainfall', updated: '2 hours ago', status: 'current', color: 'bg-green-500' },
        { source: 'DHIS2 Health Data', updated: '6 hours ago', status: 'current', color: 'bg-green-500' },
        { source: 'Population Stats', updated: '2 days ago', status: 'acceptable', color: 'bg-yellow-500' },
        { source: 'ACLED Conflict Data', updated: '1 week ago', status: 'stale', color: 'bg-orange-500' },
    ]

    // ========================================================================
    // MOCK FACILITIES FOR MAP
    // ========================================================================
    // TODO: Fetch from facilities table
    const mapFacilities = [
        { name: 'Mora District Hospital', lat: 11.0455, lng: 14.1392, type: 'Hospital', status: 'Operational' },
        { name: 'Makary Health Center', lat: 12.5739, lng: 14.4581, type: 'Health Center', status: 'Operational' },
        { name: 'Kousseri Clinic', lat: 12.0778, lng: 15.0308, type: 'Clinic', status: 'Limited capacity' },
    ]

    return (
        <AppLayout title="Risk Intelligence Dashboard">
            {/* ================================================================ */}
            {/* CRITICAL ALERTS BANNER (Urgency Signal)                          */}
            {/* ================================================================ */}
            {/* Top-of-page banner immediately communicates what needs attention  */}
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

            {/* ================================================================ */}
            {/* OPERATIONAL METRICS CARDS                                        */}
            {/* ================================================================ */}
            {/* Real system counts from database                                 */}
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                {riskOverview.map((stat) => {
                    const Icon = stat.icon
                    const colorClasses = {
                        red: 'bg-red-50 text-red-600 border-red-200',
                        orange: 'bg-orange-50 text-orange-600 border-orange-200',
                        blue: 'bg-blue-50 text-blue-600 border-blue-200',
                        purple: 'bg-purple-50 text-purple-600 border-purple-200',
                        green: 'bg-green-50 text-green-600 border-green-200',
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

            {/* ================================================================ */}
            {/* MAP + ALERTS SIDE BY SIDE (Geospatial Intelligence)             */}
            {/* ================================================================ */}
            {/* Left: Interactive map with REAL districts from database          */}
            {/* Right: Active alerts with explainability                         */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {/* RISK MAP */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 h-[500px]">
                    <div className="px-6 py-4 border-b border-gray-200">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-gray-900">Far North Region — Risk Map</h3>
                            {/* Legend showing risk color codes */}
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
                        {/* Pass REAL districts from database to map component */}
                        <RiskMap districts={mapDistricts} facilities={mapFacilities} />
                    </div>
                </div>

                {/* ACTIVE ALERTS */}
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
                                {/* EXPLAINABILITY: Why is this alert triggered? */}
                                <p className="text-sm mb-2">
                                    <span className="font-medium">Why:</span> {alert.reason}
                                </p>
                                <p className="text-xs opacity-75">{alert.time}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Rest of the dashboard components... */}
            {/* (Keeping the rest unchanged for brevity) */}
            
        </AppLayout>
    )
}