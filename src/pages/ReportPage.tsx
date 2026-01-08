import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { createItem } from '../services/api'
import '../styles/report.css'

export default function ReportPage() {
  const navigate = useNavigate()
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    status: 'lost',
    image: '',
  })
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target
    setFormData(prev => ({
      ...prev,
      [name]: value,
    }))
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')

    if (!formData.title.trim() || !formData.description.trim()) {
      setError('Please fill in all required fields')
      return
    }

    try {
      setLoading(true)
      await createItem(formData)
      navigate('/')
    } catch (err) {
      setError('Failed to report item. Please try again.')
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="report-page">
      <div className="report-container">
        <h2>Report a Lost or Found Item</h2>

        <form onSubmit={handleSubmit} className="report-form">
          {error && <div className="error-message">{error}</div>}

          <div className="form-group">
            <label htmlFor="title">Item Name *</label>
            <input
              id="title"
              type="text"
              name="title"
              placeholder="e.g., Red Wallet, Black Keys"
              value={formData.title}
              onChange={handleChange}
              required
            />
          </div>

          <div className="form-group">
            <label htmlFor="description">Description *</label>
            <textarea
              id="description"
              name="description"
              placeholder="Provide detailed information about the item..."
              rows={5}
              value={formData.description}
              onChange={handleChange}
              required
            />
          </div>

          <div className="form-group">
            <label htmlFor="status">Status</label>
            <select
              id="status"
              name="status"
              value={formData.status}
              onChange={handleChange}
            >
              <option value="lost">Lost Item</option>
              <option value="found">Found Item</option>
            </select>
          </div>

          <div className="form-group">
            <label htmlFor="image">Image URL</label>
            <input
              id="image"
              type="url"
              name="image"
              placeholder="https://example.com/image.jpg"
              value={formData.image}
              onChange={handleChange}
            />
          </div>

          <button type="submit" className="btn-submit" disabled={loading}>
            {loading ? 'Reporting...' : 'Report Item'}
          </button>
        </form>
      </div>
    </div>
  )
}
