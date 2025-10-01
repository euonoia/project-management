<section class="max-w-3xl mx-auto mt-12 bg-white rounded-xl shadow-lg p-8">
    <h2 class="text-2xl font-bold mb-6">Reservation Progress</h2>
    <?php if (empty($user_reservations)): ?>
      <div class="text-gray-500">You have no reservations yet.</div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="min-w-full border rounded-lg overflow-hidden">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 text-left font-semibold">Ref</th>
              <th class="px-4 py-2 text-left font-semibold">Date</th>
              <th class="px-4 py-2 text-left font-semibold">Pick-up</th>
              <th class="px-4 py-2 text-left font-semibold">Drop-off</th>
              <th class="px-4 py-2 text-left font-semibold">Status</th>
              <th class="px-4 py-2 text-left font-semibold">Progress</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($user_reservations as $res): ?>
            <tr class="border-t">
              <td class="px-4 py-2"><?= e($res['reservation_ref']) ?></td>
              <td class="px-4 py-2"><?= e(date('M d, Y', strtotime($res['trip_date']))) ?></td>
              <td class="px-4 py-2"><?= e(date('H:i', strtotime($res['pickup_datetime']))) ?></td>
              <td class="px-4 py-2"><?= e(date('H:i', strtotime($res['dropoff_datetime']))) ?></td>
              <td class="px-4 py-2">
                <span class="inline-block px-2 py-1 rounded bg-blue-100 text-blue-700"><?= e($res['status']) ?></span>
              </td>
              <td class="px-4 py-2">
                <div class="w-full bg-gray-200 rounded h-3">
                  <div class="bg-blue-500 h-3 rounded" style="width:<?= get_progress_percent($res['status']) ?>%"></div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>