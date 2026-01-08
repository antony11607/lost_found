import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { getItems } from '../services/api'
import ItemCard from '../components/ItemCard'
import '../styles/home.css'

interface Item {
  id: number
  title: string
  description: string
  status: string
  image: string
  created_at: string
}

export default function HomePage() {
  const [items, setItems] = useState<Item[]>([])
  const [loading, setLoading] = useState(true)
  const [filter, setFilter] = useState('all')

  useEffect(() => {
    loadItems()
  }, [filter])

  const loadItems = async () => {
    try {
      setLoading(true)
      const response = await getItems(filter !== 'all' ? filter : undefined)
      setItems(response)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="home-page">
      <div className="home-container">
        <section className="hero">
          <h2>Find Lost Items or Report Found Ones</h2>
          <p>Help reunite people with their belongings</p>
        </section>

        <div className="filters">
          <button
            className={`filter-btn ${filter === 'all' ? 'active' : ''}`}
            onClick={() => setFilter('all')}
          >
            All Items
          </button>
          <button
            className={`filter-btn ${filter === 'lost' ? 'active' : ''}`}
            onClick={() => setFilter('lost')}
          >
            Lost
          </button>
          <button
            className={`filter-btn ${filter === 'found' ? 'active' : ''}`}
            onClick={() => setFilter('found')}
          >
            Found
          </button>
        </div>

        {loading ? (
          <div className="loading">Loading items...</div>
        ) : items.length === 0 ? (
          <div className="empty-state">
            <p>No items found. {filter === 'all' && <Link to="/report">Report one!</Link>}</p>
          </div>
        ) : (
          <div className="items-grid">
            {items.map(item => (
              <ItemCard key={item.id} item={item} />
            ))}
          </div>
        )}
      </div>
    </div>
  )
}
