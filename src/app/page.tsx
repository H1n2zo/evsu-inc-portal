export default function Home() {
  return (
    <div style={{ padding: '50px', fontFamily: 'sans-serif', backgroundColor: '#f5f5f5', minHeight: '100vh' }}>
      <h1>EVSU Inc Portal - Admin Workspace</h1>
      <p>Click a link below to test the modules Claude has built so far:</p>
      
      <ul style={{ lineHeight: '2' }}>
        {/* REPLACE THE WORDS IN THE href WITH YOUR EXACT FOLDER NAMES */}
        <li><a href="/dashboard" style={{ color: 'blue', textDecoration: 'underline' }}>Go to Dashboard Module</a></li>
        <li><a href="/users" style={{ color: 'blue', textDecoration: 'underline' }}>Go to Users Module</a></li>
        <li><a href="/settings" style={{ color: 'blue', textDecoration: 'underline' }}>Go to Settings Module</a></li>
      </ul>
    </div>
  );
}