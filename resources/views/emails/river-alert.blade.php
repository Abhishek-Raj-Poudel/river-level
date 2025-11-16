<div style="max-width: 480px; margin: auto; font-family: Arial, sans-serif; background: #ffffff; border: 1px solid #e3e3e3; border-radius: 6px; padding: 20px;">

  <div style="background: #d32f2f; color: #ffffff; padding: 12px 16px; border-radius: 4px; font-size: 18px; font-weight: bold; text-align: center;">
    ⚠️ River Level Alert For — {{ $river->name }}
  </div>

  <p style="font-size: 15px; color: #333; margin-top: 20px;">
    The water level has surpassed the designated safety threshold.
  </p>

  <table style="width: 100%; border-collapse: collapse; margin: 16px 0;">
    <tr>
      <td style="padding: 10px; background: #f7f7f7; font-weight: bold; width: 50%;">Current Level:</td>
      <td style="padding: 10px; background: #f7f7f7;">{{ $river->current_water_level }}</td>
    </tr>
    <tr>
      <td style="padding: 10px; background: #ffffff; font-weight: bold;">Safe Threshold:</td>
      <td style="padding: 10px; background: #ffffff;">{{ $river->normal_water_level }}</td>
    </tr>
  </table>

  <p style="font-size: 15px; color: #444;">
    Please remain cautious and follow local safety guidance.
    <strong style="color: #d32f2f;">Immediate precautions are strongly advised.</strong>
  </p>

  <hr style="border: none; border-top: 1px solid #e3e3e3; margin: 20px 0;">

  <p style="font-size: 13px; color: #777;">
    Stay safe,<br>
    <strong>The Monitoring Team</strong>
  </p>

</div>
