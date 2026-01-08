import { Link } from 'react-router-dom'
import '../styles/header.css'

export default function Header() {
  return (
    <header className="header">
      <div className="header-container">
        <Link to="/" className="logo">
          <h1>Lost & Found</h1>
        </Link>
        <nav className="nav">
          <Link to="/" className="nav-link">Browse</Link>
          <Link to="/report" className="nav-link btn-primary">Report Item</Link>
        </nav>
      </div>
    </header>
  )
}
