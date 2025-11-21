use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('username', 'superadmin')->first();
$user->password = Hash::make('Admin123!');
$user->save();
echo "Password updated for superadmin\n";
