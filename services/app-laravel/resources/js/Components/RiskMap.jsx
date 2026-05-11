import { MapContainer, TileLayer, Circle, Popup, Marker } from 'react-leaflet'
import { useEffect } from 'react'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// Fix Leaflet default icon issue with Vite
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
})

export default function RiskMap({ districts, facilities }) {
    // Far North Region center
    const center = [10.5, 14.5]
    const zoom = 8

    // Risk level colors
    const getRiskColor = (risk) => {
        if (risk >= 8) return '#dc2626' // red-600 - Critical
        if (risk >= 6) return '#ea580c' // orange-600 - High
        if (risk >= 4) return '#eab308' // yellow-500 - Medium
        return '#16a34a' // green-600 - Low
    }

    const getRiskRadius = (risk) => {
        return risk * 3000 // Scale circle size by risk
    }

    return (
        <div className="h-full w-full rounded-lg overflow-hidden border-2 border-gray-200">
            <MapContainer
                center={center}
                zoom={zoom}
                style={{ height: '100%', width: '100%' }}
                scrollWheelZoom={true}
            >
                <TileLayer
                    attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                />

                {/* District Risk Circles */}
                {districts.map((district) => (
                    <Circle
                        key={district.name}
                        center={[district.lat, district.lng]}
                        radius={getRiskRadius(district.risk)}
                        pathOptions={{
                            fillColor: getRiskColor(district.risk),
                            fillOpacity: 0.4,
                            color: getRiskColor(district.risk),
                            weight: 2,
                        }}
                    >
                        <Popup>
                            <div className="p-2">
                                <h3 className="font-bold text-lg mb-2">{district.name}</h3>
                                <div className="space-y-1 text-sm">
                                    <p><span className="font-semibold">Risk Score:</span> {district.risk}/10</p>
                                    <p><span className="font-semibold">Status:</span> {district.status}</p>
                                    <p><span className="font-semibold">Vulnerable Children:</span> {district.population}</p>
                                    <p><span className="font-semibold">Key Factors:</span> {district.factors}</p>
                                </div>
                            </div>
                        </Popup>
                    </Circle>
                ))}

                {/* Health Facilities */}
                {facilities && facilities.map((facility) => (
                    <Marker
                        key={facility.name}
                        position={[facility.lat, facility.lng]}
                    >
                        <Popup>
                            <div className="p-2">
                                <h3 className="font-bold">{facility.name}</h3>
                                <p className="text-sm">{facility.type}</p>
                                <p className="text-xs text-gray-600 mt-1">{facility.status}</p>
                            </div>
                        </Popup>
                    </Marker>
                ))}
            </MapContainer>
        </div>
    )
}