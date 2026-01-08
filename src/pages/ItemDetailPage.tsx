import { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import { getItem, getComments, createComment } from '../services/api'
import '../styles/item-detail.css'

interface Item {
  id: number
  title: string
  description: string
  status: string
  image: string
  created_at: string
}

interface Comment {
  id: number
  content: string
  created_at: string
}

export default function ItemDetailPage() {
  const { id } = useParams<{ id: string }>()
  const [item, setItem] = useState<Item | null>(null)
  const [comments, setComments] = useState<Comment[]>([])
  const [loading, setLoading] = useState(true)
  const [commentText, setCommentText] = useState('')
  const [submitting, setSubmitting] = useState(false)

  useEffect(() => {
    if (id) {
      loadData()
    }
  }, [id])

  const loadData = async () => {
    try {
      setLoading(true)
      const [itemData, commentsData] = await Promise.all([
        getItem(Number(id)),
        getComments(Number(id)),
      ])
      setItem(itemData)
      setComments(commentsData)
    } finally {
      setLoading(false)
    }
  }

  const handleCommentSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!commentText.trim() || !id) return

    try {
      setSubmitting(true)
      await createComment(Number(id), commentText)
      setCommentText('')
      loadData()
    } finally {
      setSubmitting(false)
    }
  }

  if (loading) {
    return <div className="loading">Loading...</div>
  }

  if (!item) {
    return (
      <div className="item-detail">
        <div className="container">
          <p>Item not found</p>
          <Link to="/">Back to Browse</Link>
        </div>
      </div>
    )
  }

  return (
    <div className="item-detail">
      <div className="container">
        <Link to="/" className="back-link">← Back</Link>

        <div className="detail-content">
          <div className="detail-image">
            <img
              src={item.image || 'https://images.pexels.com/photos/3454496/pexels-photo-3454496.jpeg'}
              alt={item.title}
            />
            <span className={`status-badge status-${item.status}`}>
              {item.status === 'lost' ? 'Lost Item' : 'Found Item'}
            </span>
          </div>

          <div className="detail-info">
            <h1>{item.title}</h1>
            <p className="date">
              {new Date(item.created_at).toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric',
              })}
            </p>
            <div className="description">
              <h3>Description</h3>
              <p>{item.description}</p>
            </div>
          </div>
        </div>

        <div className="comments-section">
          <h3>Comments & Tips</h3>

          <form onSubmit={handleCommentSubmit} className="comment-form">
            <textarea
              placeholder="Share information or tips about this item..."
              value={commentText}
              onChange={e => setCommentText(e.target.value)}
              rows={4}
            />
            <button type="submit" disabled={submitting || !commentText.trim()}>
              {submitting ? 'Posting...' : 'Post Comment'}
            </button>
          </form>

          <div className="comments-list">
            {comments.length === 0 ? (
              <p className="no-comments">No comments yet. Be the first to comment!</p>
            ) : (
              comments.map(comment => (
                <div key={comment.id} className="comment">
                  <p>{comment.content}</p>
                  <span className="comment-date">
                    {new Date(comment.created_at).toLocaleDateString()}
                  </span>
                </div>
              ))
            )}
          </div>
        </div>
      </div>
    </div>
  )
}
