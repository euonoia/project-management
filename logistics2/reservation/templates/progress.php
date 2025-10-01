<div class="max-w-4xl mx-auto px-4">

    <!-- Heading row: left-aligned title -->
    <div class="flex justify-start mb-8">
        <h3 class="text-2xl font-bold text-gray-800">Active Reservations</h3>
    </div>

    <?php if (empty($active_reservations)): ?>
        <div class="text-gray-500 text-center py-20">You have no active reservations.</div>
    <?php else: ?>
        <!-- Stack cards vertically, full width -->
        <div class="space-y-6">
            <?php foreach ($active_reservations as $res): ?>
                <?php
                    $status = strtolower($res['status']);
                    $statusData = [
                        'pending' => ['color' => 'bg-yellow-100 text-yellow-800', 'icon' => 'fas fa-hourglass-half'],
                        'dispatched' => ['color' => 'bg-blue-100 text-blue-800', 'icon' => 'fas fa-truck']
                    ];
                    $badgeColor = $statusData[$status]['color'] ?? 'bg-gray-100 text-gray-700';
                    $statusIcon = $statusData[$status]['icon'] ?? 'fas fa-circle';
                ?>
                
                <div class="w-full bg-white rounded-xl shadow-lg hover:shadow-2xl transition-transform transform hover:-translate-y-1 p-6 flex flex-col justify-between">

                    <!-- Reservation Info -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold text-lg text-gray-900"><?= e($res['reservation_ref']) ?></h4>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold <?= $badgeColor ?>">
                                <i class="<?= $statusIcon ?> mr-2"></i>
                                <?= ucfirst($res['status']) ?>
                            </span>
                        </div>

                        <p class="text-gray-500 text-sm mb-2"><?= e(date('M d, Y', strtotime($res['trip_date']))) ?></p>

                        <div class="text-gray-600 text-sm space-y-2">
                          <div class="flex items-center gap-3">
                              <i class="fas fa-map-marker-alt text-red-500"></i>
                              <span><strong>Pick-up:</strong> <?= e($res['pickup_location']) ?></span>
                          </div>
                          <div class="flex items-center gap-3">
                              <i class="fas fa-flag-checkered text-green-500"></i>
                              <span><strong>Drop-off:</strong> <?= e($res['dropoff_location']) ?></span>
                          </div>
                          <div class="flex items-center gap-3">
                              <i class="fas fa-clock text-gray-400"></i>
                              <span><strong>Time:</strong> <?= e(date('H:i', strtotime($res['pickup_datetime']))) ?> - <?= e(date('H:i', strtotime($res['dropoff_datetime']))) ?></span>
                          </div>
                      </div>

                    </div>

                    <!-- Optional Action Buttons -->
                   <div class="mt-4 flex justify-center space-x-3">
                      <?php if ($status === 'pending'): ?>
                          <button class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition w-full md:w-auto">View Details</button>
                          <button class="px-4 py-2 bg-red-600 text-white rounded-lg shadow hover:bg-red-700 transition w-full md:w-auto">Cancel</button>
                      <?php elseif ($status === 'dispatched'): ?>
                          <button class="px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition w-full md:w-auto">Track Vehicle</button>
                      <?php endif; ?>
                  </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
