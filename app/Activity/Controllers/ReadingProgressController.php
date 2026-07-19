<?php

namespace BookStack\Activity\Controllers;

use BookStack\Activity\Models\ReadingProgress;
use BookStack\Activity\Services\ReadingProgressService;
use BookStack\Entities\Queries\PageQueries;
use BookStack\Http\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReadingProgressController extends Controller
{
    public function __construct(
        protected ReadingProgressService $readingProgressService,
        protected PageQueries $pageQueries,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request, int $pageId): JsonResponse
    {
        $this->validate($request, [
            'progress_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'scroll_position' => ['required', 'integer', 'min:0'],
        ]);

        $page = $this->pageQueries->findVisibleByIdOrFail($pageId);
        $user = user();
        $existingRecord = $this->readingProgressService->getForUserAndPage($user, $page);
        if ($existingRecord) {
            $this->authorize('update', $existingRecord);
        }

        $record = $this->readingProgressService->updateOrCreate(
            $user,
            $page,
            (int) $request->input('progress_percentage'),
            (int) $request->input('scroll_position')
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'progress_percentage' => $record->progress_percentage,
                'scroll_position' => $record->scroll_position,
                'completed' => (bool) $record->completed,
            ],
        ]);
    }

    public function show(int $pageId): JsonResponse
    {
        $page = $this->pageQueries->findVisibleByIdOrFail($pageId);
        $record = $this->readingProgressService->getForUserAndPage(user(), $page);

        return response()->json([
            'status' => 'success',
            'data' => $record ? [
                'progress_percentage' => $record->progress_percentage,
                'scroll_position' => $record->scroll_position,
                'completed' => (bool) $record->completed,
                'last_opened_at' => $record->last_opened_at,
            ] : null,
        ]);
    }

    public function continueReading(): JsonResponse
    {
        $records = $this->readingProgressService->continueReadingForUser(user(), 10);

        return response()->json([
            'status' => 'success',
            'data' => $records->map(function (ReadingProgress $record) {
                return [
                    'id' => $record->id,
                    'page_id' => $record->page_id,
                    'page_name' => $record->page->name,
                    'book_name' => $record->page->book->name,
                    'chapter_name' => $record->page->chapter?->name,
                    'progress_percentage' => $record->progress_percentage,
                    'last_opened_at' => $record->last_opened_at?->toIso8601String(),
                    'url' => $record->page->getUrl(),
                ];
            })->values(),
        ]);
    }
}
