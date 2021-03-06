<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D is open-sourced software licensed under the GNU General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D
 *
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 * @author     HDVinnie
 */

namespace App\Http\Controllers\Staff;

use App\Models\Note;
use App\Models\User;
use Brian2694\Toastr\Toastr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NoteController extends Controller
{
    /**
     * @var Toastr
     */
    private $toastr;

    /**
     * NoteController Constructor.
     *
     * @param Toastr $toastr
     */
    public function __construct(Toastr $toastr)
    {
        $this->toastr = $toastr;
    }

    /**
     * Get All User Notes.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getNotes()
    {
        $notes = Note::latest()->paginate(25);

        return view('Staff.notes.index', ['notes' => $notes]);
    }

    /**
     * Post A User Note.
     *
     * @param \Illuminate\Http\Request $request
     * @param $username
     * @param $id
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function postNote(Request $request, $username, $id)
    {
        $staff = auth()->user();
        $user = User::findOrFail($id);

        $note = new Note();
        $note->user_id = $user->id;
        $note->staff_id = $staff->id;
        $note->message = $request->input('message');

        $v = validator($note->toArray(), [
            'user_id'  => 'required',
            'staff_id' => 'required',
            'message'  => 'required',
        ]);

        if ($v->fails()) {
            return redirect()->route('profile', ['username' => $user->username, 'id' => $user->id])
                ->with($this->toastr->error($v->errors()->toJson(), 'Whoops!', ['options']));
        } else {
            $note->save();

            // Activity Log
            \LogActivity::addToLog("Staff Member {$staff->username} has added a note on {$user->username} account.");

            return redirect()->route('profile', ['username' => $user->username, 'id' => $user->id])
                ->with($this->toastr->success('Note Has Successfully Posted', 'Yay!', ['options']));
        }
    }

    /**
     * Delete A User Note.
     *
     * @param $id
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function deleteNote($id)
    {
        $note = Note::findOrFail($id);
        $note->delete();

        return redirect()->back()
            ->with($this->toastr->success('Note Has Successfully Been Deleted', 'Yay!', ['options']));
    }
}
