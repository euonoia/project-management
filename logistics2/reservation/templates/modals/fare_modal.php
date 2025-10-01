<!-- Transport Cost Analysis Modal -->
<div id="fareModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 sm:mx-auto overflow-hidden relative">
        <button onclick="closeFareModal()" class="absolute top-4 right-6 text-2xl text-gray-400 hover:text-red-500">&times;</button>
        <h3 class="text-xl font-bold text-blue-700 px-6 pt-6">Transport Cost Analysis</h3>
        <div id="fareModalBody" class="px-6 py-4"></div>
        <div class="px-6 py-4 border-t flex justify-end bg-gray-50 gap-2">
            <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300" type="button" onclick="closeFareModal()">Cancel</button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" type="button" onclick="submitReservation()">Confirm Reservation</button>
        </div>
    </div>
</div>
