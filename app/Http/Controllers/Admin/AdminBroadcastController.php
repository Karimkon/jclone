<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendAdminBroadcastJob;
use App\Models\AdminBroadcast;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminBroadcastController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminBroadcast::with('creator')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('audience')) {
            $query->where('audience', $request->audience);
        }

        $broadcasts = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => AdminBroadcast::count(),
            'sent'  => AdminBroadcast::where('status', 'sent')->count(),
            'draft' => AdminBroadcast::where('status', 'draft')->count(),
        ];

        return view('admin.broadcasts.index', compact('broadcasts', 'stats'));
    }

    public function create()
    {
        return view('admin.broadcasts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'body'      => 'required|string',
            'image_url' => 'nullable|url|max:500',
            'route'     => 'nullable|string|max:255',
            'audience'  => 'required|in:all,buyers,vendors,specific_user',
            'user_id'   => 'required_if:audience,specific_user|nullable|exists:users,id',
            'action'    => 'required|in:draft,send',
        ]);

        $totalRecipients = $this->countAudience($validated['audience'], $validated['user_id'] ?? null);

        $broadcast = AdminBroadcast::create([
            'title'             => $validated['title'],
            'body'              => $validated['body'],
            'image_url'         => $validated['image_url'] ?? null,
            'route'             => $validated['route'] ?? '/notifications',
            'audience'          => $validated['audience'],
            'user_id'           => $validated['user_id'] ?? null,
            'total_recipients'  => $totalRecipients,
            'status'            => 'draft',
            'created_by'        => Auth::id(),
        ]);

        if ($validated['action'] === 'send') {
            SendAdminBroadcastJob::dispatch($broadcast);
            $broadcast->update(['status' => 'sending']);

            return redirect()->route('admin.broadcasts.index')
                ->with('success', 'Broadcast is being sent to ' . number_format($totalRecipients) . ' users!');
        }

        return redirect()->route('admin.broadcasts.index')
            ->with('success', 'Broadcast saved as draft.');
    }

    public function cancel(AdminBroadcast $broadcast)
    {
        if (!in_array($broadcast->status, ['draft', 'sending'])) {
            return redirect()->route('admin.broadcasts.index')
                ->with('error', 'This broadcast cannot be cancelled.');
        }

        $broadcast->update(['status' => 'failed']);

        return redirect()->route('admin.broadcasts.index')
            ->with('success', 'Broadcast has been cancelled.');
    }

    public function getAudienceCount(Request $request)
    {
        $request->validate([
            'audience' => 'required|in:all,buyers,vendors,specific_user',
            'user_id'  => 'nullable|exists:users,id',
        ]);

        $count = $this->countAudience($request->audience, $request->user_id);

        return response()->json(['count' => $count]);
    }

    public function searchUsers(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2']);

        $users = User::where(function ($query) use ($request) {
                $query->where('email', 'like', '%' . $request->q . '%')
                      ->orWhere('name', 'like', '%' . $request->q . '%');
            })
            ->select('id', 'name', 'email', 'role')
            ->limit(10)
            ->get();

        return response()->json($users);
    }

    protected function countAudience(string $audience, ?int $userId): int
    {
        return match ($audience) {
            'buyers'        => User::where('role', 'buyer')->count(),
            'vendors'       => User::whereIn('role', ['vendor_local', 'vendor_international'])->count(),
            'specific_user' => $userId ? 1 : 0,
            default         => User::count(), // all
        };
    }
}
