import { Link } from 'react-router-dom'
import '../styles/item-card.css'

interface Item {
  id: number
  title: string
  description: string
  status: string
  image: string
  created_at: string
}

export default function ItemCard({ item }: { item: Item }) {
  const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    })
  }

  return (
    <Link to={`/item/${item.id}`} className="item-card">
      <div className="item-image">
        <img src={item.image || 'https://images.pexels.com/photos/3454496/pexels-photo-3454496.jpeg'} alt={item.title} />
        <span className={`status-badge status-${item.status}`}>
          {item.status === 'lost' ? 'Lost' : 'Found'}
        </span>
      </div>
      <div className="item-content">
        <h3>{item.title}</h3>
        <p className="description">{item.description.substring(0, 100)}...</p>
        <p className="date">{formatDate(item.created_at)}</p>
      </div>
    </Link>
  )
}
