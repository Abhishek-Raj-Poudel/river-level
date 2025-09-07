import { useEffect } from "react";

export function useRiverAlerts() {
  useEffect(() => {
    const echo = (window as any).Echo;
    if (!echo) return;

    const channel = echo.channel("river-levels")
      .listen(".river.level.exceeded", (e: any) => {
        console.log("⚠️ River exceeded:", e.river);
        alert(`River ${e.river.river_name} exceeded threshold!`);
      });

    return () => {
      echo.leaveChannel("river-levels");
    };
  }, []);
}
