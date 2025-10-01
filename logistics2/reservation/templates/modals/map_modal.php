<!-- Map Modal -->
<div id="mapModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 sm:mx-auto overflow-hidden relative">
        <div class="flex justify-between items-center bg-gray-100 px-6 py-4 border-b">
            <strong id="mapModalTitle" class="text-lg font-semibold text-blue-700">Select Pick-up Location</strong>
            <button type="button" id="closeMapModal" class="text-gray-600 hover:text-red-500 text-2xl font-bold">&times;</button>
        </div>
        <div class="p-6 space-y-4">
            <form id="mapSearchForm" class="flex gap-2" onsubmit="return false;">
                <input type="search" id="mapSearch" class="flex-1 border rounded px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                       placeholder="Search PH address (e.g., dominguez st, malibay, pasay city, metro manila)" autocomplete="off">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" id="mapSearchBtn">Search</button>
            </form>
            <div id="map" class="w-full h-96 rounded-lg border"></div>
            <div id="selected-info" class="text-gray-700 text-sm">
                <strong>Selected:</strong> <span id="address-text">Drag marker, click map, or search.</span>
            </div>
        </div>
        <div class="px-6 py-4 border-t flex justify-end bg-gray-50">
            <button type="button" id="useLocationBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Use this location</button>
        </div>
    </div>
</div>
