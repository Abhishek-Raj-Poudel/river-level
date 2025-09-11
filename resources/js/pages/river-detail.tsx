import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { River, getStatusColor, getStatusText } from "@/data/rivers";
import { ArrowLeft, Droplets, TrendingUp, Thermometer, MapPin, Calendar, Activity } from "lucide-react";
import { cn } from "@/lib/utils";
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, AreaChart, Area } from 'recharts';

interface RiverDetailProps {
  river: River;
}

export const RiverDetail = ({ river }: RiverDetailProps) => {
    console.log(river);
  const lastUpdated = new Date(river.last_updated).toLocaleString();

  return (
    <div className="min-h-screen bg-gradient-background">
      <div className="container mx-auto px-4 py-8 max-w-6xl">

        {/* Title Section */}
        <div className="mb-8">
          <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
            <div>
              <h1 className="text-4xl font-bold text-foreground mb-2">{river.name}</h1>
              <div className="flex items-center gap-4 text-muted-foreground">
                <div className="flex items-center gap-1">
                  <MapPin className="w-4 h-4" />
                  {river.country}, {river.continent}
                </div>
                <div className="flex items-center gap-1">
                  <Calendar className="w-4 h-4" />
                  Updated {lastUpdated}
                </div>
              </div>
            </div>
            <Badge
              variant="secondary"
              className={cn(
                "text-sm font-medium px-4 py-2",
                river.water_level_status === 'low' && "bg-water-low/20 text-water-low border-water-low/30",
                river.water_level_status === 'normal' && "bg-water-normal/20 text-water-normal border-water-normal/30",
                river.water_level_status === 'high' && "bg-water-high/20 text-water-high border-water-high/30",
                river.water_level_status === 'critical' && "bg-water-critical/20 text-water-critical border-water-critical/30"
              )}
            >
              {getStatusText(river.water_level_status)}
            </Badge>
          </div>
          <p className="text-muted-foreground text-lg max-w-3xl">{river.description}</p>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <Card className="bg-gradient-card border-border/50 p-6">
            <div className="flex items-center gap-3 mb-3">
              <div className="p-2 rounded-lg bg-primary/20">
                <Droplets className="w-5 h-5 text-primary" />
              </div>
              <h3 className="font-semibold text-foreground">Water Level</h3>
            </div>
            <div className="text-3xl font-bold text-foreground mb-1">
              {river.water_level_current}m
            </div>
            <div className="text-sm text-muted-foreground">
              Normal: {river.water_level_normal}m
            </div>
            <div className="mt-3 w-full bg-muted rounded-full h-2">
              <div
                className={cn(
                  "h-2 rounded-full transition-all duration-500",
                  `bg-${getStatusColor(river.water_level_status)}`
                )}
                style={{
                  width: `${Math.min(100, (river.water_level_current / river.water_level_normal) * 100)}%`
                }}
              />
            </div>
          </Card>

          <Card className="bg-gradient-card border-border/50 p-6">
            <div className="flex items-center gap-3 mb-3">
              <div className="p-2 rounded-lg bg-accent/20">
                <TrendingUp className="w-5 h-5 text-accent" />
              </div>
              <h3 className="font-semibold text-foreground">Flow Rate</h3>
            </div>
            <div className="text-3xl font-bold text-foreground mb-1">
              {river.flow_rate_current.toLocaleString()}
            </div>
            <div className="text-sm text-muted-foreground">
              m³/s • Avg: {river.flow_rate_average.toLocaleString()}
            </div>
          </Card>

          <Card className="bg-gradient-card border-border/50 p-6">
            <div className="flex items-center gap-3 mb-3">
              <div className="p-2 rounded-lg bg-warning/20">
                <Thermometer className="w-5 h-5 text-warning" />
              </div>
              <h3 className="font-semibold text-foreground">Temperature</h3>
            </div>
            <div className="text-3xl font-bold text-foreground mb-1">
              {river.temperature}°C
            </div>
            <div className="text-sm text-muted-foreground">
              Current water temp
            </div>
          </Card>

          <Card className="bg-gradient-card border-border/50 p-6">
            <div className="flex items-center gap-3 mb-3">
              <div className="p-2 rounded-lg bg-secondary/20">
                <Activity className="w-5 h-5 text-secondary-foreground" />
              </div>
              <h3 className="font-semibold text-foreground">River Length</h3>
            </div>
            <div className="text-3xl font-bold text-foreground mb-1">
              {river.length.toLocaleString()}
            </div>
            <div className="text-sm text-muted-foreground">
              kilometers
            </div>
          </Card>
        </div>

        {/* Charts */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <Card className="bg-gradient-card border-border/50 p-6">
            <h3 className="text-xl font-bold text-foreground mb-6">Weekly Water Levels</h3>
            <ResponsiveContainer width="100%" height={300}>
              <AreaChart data={river.weeklyData}>
                <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                <XAxis
                  dataKey="day"
                  stroke="hsl(var(--muted-foreground))"
                  fontSize={12}
                />
                <YAxis
                  stroke="hsl(var(--muted-foreground))"
                  fontSize={12}
                />
                <Tooltip
                  contentStyle={{
                    backgroundColor: 'hsl(var(--card))',
                    border: '1px solid hsl(var(--border))',
                    borderRadius: '8px',
                    color: 'hsl(var(--foreground))'
                  }}
                />
                <Area
                  type="monotone"
                  dataKey="level"
                  stroke="hsl(var(--primary))"
                  fill="hsl(var(--primary) / 0.2)"
                  strokeWidth={2}
                />
              </AreaChart>
            </ResponsiveContainer>
          </Card>

          <Card className="bg-gradient-card border-border/50 p-6">
            <h3 className="text-xl font-bold text-foreground mb-6">Weekly Flow Rates</h3>
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={river.weeklyData}>
                <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                <XAxis
                  dataKey="day"
                  stroke="hsl(var(--muted-foreground))"
                  fontSize={12}
                />
                <YAxis
                  stroke="hsl(var(--muted-foreground))"
                  fontSize={12}
                />
                <Tooltip
                  contentStyle={{
                    backgroundColor: 'hsl(var(--card))',
                    border: '1px solid hsl(var(--border))',
                    borderRadius: '8px',
                    color: 'hsl(var(--foreground))'
                  }}
                />
                <Line
                  type="monotone"
                  dataKey="flow"
                  stroke="hsl(var(--accent))"
                  strokeWidth={3}
                  dot={{ fill: 'hsl(var(--accent))', strokeWidth: 2, r: 4 }}
                />
              </LineChart>
            </ResponsiveContainer>
          </Card>
        </div>

        {/* Additional Info */}
        <Card className="bg-gradient-card border-border/50 p-6 mt-8">
          <h3 className="text-xl font-bold text-foreground mb-4">Location Information</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 className="font-semibold text-foreground mb-2">Coordinates</h4>
              <p className="text-muted-foreground">
                Latitude: {river.lat}°<br />
                Longitude: {river.lng}°
              </p>
            </div>
            <div>
              <h4 className="font-semibold text-foreground mb-2">Status Details</h4>
              <p className="text-muted-foreground">
                Current level is {((river.water_level_current / river.water_level_normal) * 100).toFixed(1)}% of normal levels.
                {river.water_level_status === 'critical' && ' Immediate attention required.'}
                {river.water_level_status === 'high' && ' Monitoring recommended.'}
                {river.water_level_status === 'low' && ' Below average levels.'}
              </p>
            </div>
          </div>
        </Card>
      </div>
    </div>
  );
};
