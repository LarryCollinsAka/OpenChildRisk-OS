import { Link } from '@inertiajs/react'
import { 
    LayoutDashboard, 
    AlertTriangle, 
    Users, 
    MapPin, 
    BarChart3, 
    Database, 
    Settings,
    Target,
    Activity,
    FileText
} from 'lucide-react'

export default function AppLayout({ children, title }) {
    const navigation = [
        { name: 'Dashboard', href: '/', icon: LayoutDashboard, current: true },
        { name: 'Risk Assessment', href: '/risk', icon: AlertTriangle, current: false },
        { name: 'Districts', href: '/districts', icon: MapPin, current: false },
        { name: 'Indicators', href: '/indicators', icon: BarChart3, current: false },
        { name: 'Population Groups', href: '/population-groups', icon: Users, current: false },
        { name: 'Priority Targets', href: '/priority-targets', icon: Target, current: false },
        { name: 'Field Workers', href: '/field-workers', icon: Activity, current: false },
        { name: 'Data Sources', href: '/data-sources', icon: Database, current: false },
        { name: 'Reports', href: '/reports', icon: FileText, current: false },
    ]

    return (
        <div className="min-h-screen bg-gray-50">
            {/* Sidebar */}
            <div className="fixed inset-y-0 left-0 w-64 bg-white border-r border-gray-200">
                {/* Logo */}
                <div className="flex items-center h-16 px-6 border-b border-gray-200">
                    <div className="flex items-center space-x-3">
                        <div className="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <span className="text-white font-bold text-sm">OCR</span>
                        </div>
                        <div>
                            <div className="text-sm font-semibold text-gray-900">OpenChildRisk</div>
                            <div className="text-xs text-gray-500">Intelligence Platform</div>
                        </div>
                    </div>
                </div>

                {/* Navigation */}
                <nav className="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    {navigation.map((item) => {
                        const Icon = item.icon
                        return (
                            <Link
                                key={item.name}
                                href={item.href}
                                className={`
                                    flex items-center px-3 py-2 text-sm font-medium rounded-lg
                                    transition-colors duration-150
                                    ${item.current 
                                        ? 'bg-blue-50 text-blue-700' 
                                        : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                                    }
                                `}
                            >
                                <Icon className="w-5 h-5 mr-3" />
                                {item.name}
                            </Link>
                        )
                    })}
                </nav>

                {/* User Menu */}
                <div className="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 bg-white">
                    <div className="flex items-center">
                        <div className="flex-shrink-0">
                            <div className="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                <span className="text-gray-600 font-medium text-sm">AD</span>
                            </div>
                        </div>
                        <div className="ml-3 flex-1">
                            <div className="text-sm font-medium text-gray-900">Admin User</div>
                            <div className="text-xs text-gray-500">admin@unicef.org</div>
                        </div>
                        <Settings className="w-5 h-5 text-gray-400 hover:text-gray-600 cursor-pointer" />
                    </div>
                </div>
            </div>

            {/* Main Content */}
            <div className="pl-64">
                {/* Top Bar */}
                <div className="sticky top-0 z-10 flex h-16 bg-white border-b border-gray-200">
                    <div className="flex flex-1 justify-between px-8">
                        <div className="flex flex-1 items-center">
                            <h1 className="text-xl font-semibold text-gray-900">
                                {title || 'Dashboard'}
                            </h1>
                        </div>
                        <div className="ml-4 flex items-center space-x-4">
                            {/* Breadcrumb / Actions can go here */}
                            <div className="flex items-center space-x-2">
                                <span className="px-3 py-1 text-xs font-medium text-green-700 bg-green-50 rounded-full">
                                    System Online
                                </span>
                                <span className="px-3 py-1 text-xs font-medium text-blue-700 bg-blue-50 rounded-full">
                                    Phase 2 Active
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Page Content */}
                <main className="p-8">
                    {children}
                </main>
            </div>
        </div>
    )
}